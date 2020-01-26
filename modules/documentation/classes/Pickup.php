<?php
/**
 * Created by PhpStorm.
 * User: Эдуард Бибик
 * Date: 18.01.2020
 * Time: 16:34
 */

namespace Doc;

use \Johncms\NavChain;


class Pickup
{
    /**
     * @param NavChain $nav_chain
     * @param $chains
     */
    public static function Breadcrumbs(NavChain $nav_chain, $chains): void {
        if (is_array($chains)) {
            foreach ($chains as $chain => $uri) {
                if (is_integer($chain)) {
                    $chain = $uri;
                    $uri = '';
                }
                $nav_chain->add($chain, $uri);
            }
        } elseif (is_string($chains)) {
            $nav_chain->add($chains);
        }
    }
}
