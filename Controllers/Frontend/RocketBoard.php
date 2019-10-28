<?php

/**
 * Frontend controller
 */
class Shopware_Controllers_Frontend_RocketBoard extends Enlight_Controller_Action
{
    protected $apiVersion = "1.0";

    /**
     * Default index action
     * Outputs JSON data.
     * @return void
     */
    public function indexAction()
    {
        $aConfig = $this->getPluginConfig();
        $token = $aConfig['rocketToken'];
        $reqToken = $this->Request()->getParam('rocketToken', null);
        if ($token) {
            if (!$reqToken || $reqToken != $token) {
                die("Configuration token does not match parameter 'rocketToken'!");
            }
        } else {
            die("Please set a token in the configuration!");
        }

        $pretty = $this->Request()->getParam('pretty', false);
        $toUtf8 = $this->Request()->getParam('toUtf8', false);
        $what = $this->Request()->getParam('what', 'shopware');
        switch ($what) {
            case 'shopware':
                $data = $this->getAppInfo($what);
                break;
            case 'plugins':
                $data = $this->getPluginInfo($what);
                break;
        }

        array_walk_recursive($data, function (&$value) {
            // Convert DateTime instances to ISO-8601 Strings
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ISO8601);
            }
        });

        if ($toUtf8) {
            $data = $this->utf8ize($data);
        }

        $data = Zend_Json::encode($data);
        if ($pretty) {
            $data = Zend_Json::prettyPrint($data);
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setHeader('Content-type', 'application/json', true);
        $this->Response()->setBody($data);
    }

    /**
     * Function to get all plugins and their versions.
     * @param string $what The type to return
     * @return array
     */
    public function getPluginInfo($what)
    {
        $data = array();
        
        $data['type'] = $what;
        $data['version'] = $this->apiVersion;
        $data['data'] = [];

        $select = Shopware()->Db()->select()->from(
            's_core_plugins',
            ['id', 'version', 'name', 'namespace', 'source', 'author', 'active']
        );

        $rows = Shopware()->Db()->fetchAll($select);

        foreach ($rows as $key => $row) {
            $data['data'][$rows[$key]['id']] = array(
                'name' => $rows[$key]['name'],
                'version' => $rows[$key]['version'],
                'author' => $rows[$key]['author'],
                'active' => $rows[$key]['active'],
            );
        }

        return $data;
    }
    /**
     * @param $mixed
     *
     * @return array|bool|string
     */
    public function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[ $key ] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }

        return $mixed;
    }

    /**
     * Function to get app info.
     * @param string $what The type to return
     * @return array
     */
    public function getAppInfo($what)
    {
        $shop = $this->getShop();
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $shopData = $this->get('shopware.benchmark_bundle.providers.shopware')->getBenchmarkData($context);

        $data = array();
        $data['version'] = $this->apiVersion;
        $data['type'] = $what;
        $data['application'] = [];
        $data['application']['type'] = "shopware";
        $shopEmail = Shopware()->Db()->fetchOne("SELECT cv.value
            FROM `s_core_config_elements` ce LEFT JOIN `s_core_config_values` cv
            ON ce.id=cv.element_id
            WHERE ce.label LIKE '%Shopbetreiber%'
            AND ce.`name` = 'mail'");
        $data['application']['contact'] = unserialize($shopEmail);
        $select = Shopware()->Db()->select()->from(
            's_core_shops',
            ['id', 'title', 'name', 'host', 'secure']
        )->where("id=?", $shop->getId());
        $row = Shopware()->Db()->fetchRow($select);
        $data['application']['name'] = $row['title'] != '' ? $row['title'] : $row['name'];
        $data['application']['edition'] = $shopData['licence'];
        $data['application']['version'] = str_replace('-', '', $shopData['version']);
        $data['application']['build'] = $shopData['revision'];
        $proto = $row['secure'] ? "https://" : "http://";
        $data['application']['url'] = $proto . $row['host'];

        $data['infrastructure'] = [];
        $data['infrastructure']['platform'] = 'PHP ' . phpversion();
        $data['infrastructure']['platform_info'] = $this->phpinfo2array();
        $data['infrastructure']['os'] = $shopData['os'] . " " . $shopData['arch'] . " " . $shopData['dist'] . " ";
        $data['infrastructure']['db'] = "MySQL " . $shopData['mysqlVersion'];
        $data['infrastructure']['web'] = $shopData['serverSoftware'];

        return $data;
    }

    /**
     * Get our shop specific plugin config
     *
     * @return array
     */
    private function getPluginConfig()
    {
        // get subshop specific config
        $shop = $this->getShop();
        $pluginConfig = Shopware()->Container()->get('shopware.plugin.cached_config_reader')->getByPluginName('RocketBoard', $shop);
        return $pluginConfig;
    }
    /**
     * Get active shop
     *
     * @return void
     */
    private function getShop()
    {
        $shop = false;
        if ($this->container->initialized('shop')) {
            $shop = $this->container->get('shop');
        }
        if (!$shop) {
            $shop = $this->container->get('models')->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveDefault();
        }
        return $shop;
    }

    /**
     * Function to convert the phpinfo() output to an array
     *
     * @return array
     */
    private function phpinfo2array()
    {
        $entitiesToUtf8 = function ($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $input);
        };
        $plainText = function ($input) use ($entitiesToUtf8) {
            return trim(html_entity_decode($entitiesToUtf8(strip_tags($input))));
        };
        $titlePlainText = function ($input) use ($plainText) {
            return $plainText($input);
        };
       
        ob_start();
        phpinfo(-1);
       
        $phpinfo = array();
    
        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if (!preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
            return array();
        }
       
        $input = $matches[1];
        $matches = array();
    
        if (preg_match_all(
            '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|'.
            '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
            $input,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $fn = strpos($match[0], '<th') === false ? $plainText : $titlePlainText;
                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = array();
                } elseif (isset($match[3])) {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4]) ? array($fn($match[3]), $fn($match[4])) : $fn($match[3]);
                } else {
                    $keys1 = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }
            }
        }
       
        return $phpinfo;
    }
}
