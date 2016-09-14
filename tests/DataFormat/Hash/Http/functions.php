<?php

namespace Imhonet\Connection\Response {

    function curl_multi_info_read($mh, &$msgs_in_queue = null)
    {
        unset($msgs_in_queue);

        return ['msg' => \CURLMSG_DONE, 'result' => \CURLE_OK, 'handle' => $mh];
    }

    function curl_getinfo($ch, $opt = null)
    {
        switch ($opt) {
            case \CURLINFO_PRIVATE:
                $result = \curl_getinfo($ch, $opt);
                break;
            case null:
                $result = array();
                break;
            default:
                $result = null;
        }

        return $result;
    }

    function curl_multi_remove_handle($mh, $ch)
    {
    }
}

namespace Imhonet\Connection\DataFormat\Generic {

    function curl_multi_getcontent($ch)
    {
        unset($ch);
        /** @type \JsonTest|\XmlAttrTest $provider */
        $provider = getenv('TEST_CLASS');

        return $provider::getData();
    }
}
