<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty plugin:
 * This is a Smarty plugin to create a multi-level menu using nasted ul lists.
 * The generated structure looks like this.
 * <ul $htmlOptions>
 *      <li $itemOptions>
 *          <elem></elem>
 *          <ul $submenuHtmlOptions>
 *              <li $itemOptions></li>
 *              <li $itemOptions>
 *                  <elem></elem>
 *                  <ul $submenuHtmlOptions>
 *                      <li $itemOptions></li>
 *                      ...
 *                  </ul>
 *              </li>
 *              ...
 *          </ul>
 *      </li>
 *      ...
 *  </ul>
 *
 *
 * @param $params array - look up the bellow example
 * @param $smarty
 * @return string - generated HTML code
 *
 * <pre>
 * smarty_function_sugar_menu(array(
 *      'id' => $string, //id property that is applied in root UL
 *      'items' => array(
 *          array(
 *              'html' => $html_string, //html container that renders in the LI tag
 *              'items' => array(), //nasted ul lists
 *          )
 *      ),
 *      'htmlOptions' => attributes that is applied in root UL, such as class, or align.
 *      'itemOptions' => attributes that is applied in LI items, such as class, or align.
 *      'submenuHtmlOptions' => attributes that is applied in child UL, such as class, or align.
 * ), $smarty);
 *
 * </pre>
 * * @author Justin Park (jpark@sugarcrm.com)
 */
function smarty_function_sugar_menu($params, &$smarty)
{
    $root_options = array(
        "id" => array_key_exists('id', $params) ? $params['id'] : ""
    );
    if(array_key_exists('htmlOptions', $params)) {
        foreach($params['htmlOptions'] as $attr => $value) {
            $root_options[$attr] = $value;
        }
    }
    $output = open_tag("ul", $root_options);
    foreach($params['items'] as $item) {
        if(strpos($item['html'], "</") === 0) {
            $output .= $item['html'];
            continue;
        }
        $output .= open_tag('li', !empty($params['itemOptions']) ? $params['itemOptions'] : array()).$item['html'];
        if(isset($item['items']) && count($item['items'])) {
            $output .= smarty_function_sugar_menu(array(
                'items' => $item['items'],
                'htmlOptions' => !empty($params['submenuHtmlOptions']) ? $params['submenuHtmlOptions'] : array()
            ), $smarty);
        }
        $output .= "</li>";
    }
    $output .= '</ul>';
    return $output;
}

function open_tag($tagName, $params = array(), $self_closing = false) {

    $options = "";
    $self_closing_tag = ($self_closing) ? "/" : "";
    if(empty($params))
        return "<{$tagName}{$self_closing_tag}>";

    foreach($params as $attr => $value) {
        if($value)
            $options .= $attr.'="'.$value.'" ';
    }
    return "<{$tagName} {$options}{$self_closing_tag}>";
}

function parse_html_tag($code) {
    $SINGLE_QUOTE = "'";
    $DOUBLE_QUOTE = '"';
    $ASSIGN_SIGN = "=";
    $TAG_BEGIN = "<";
    $TAG_END = ">";
    $quote_encoded = false;
    $cache = array();
    $var_name = '';
    $var_assign = false;
    $start_pos = strpos($code, ' ') ? strpos($code, ' ') : strpos($code, $TAG_END);
    if(substr($code, 0, 1) != $TAG_BEGIN || $start_pos === false) {
        return $code;
    }
    $tag = substr($code, 1, $start_pos - 1);
    $closing_tag = '</'.$tag;
    $end_pos = strpos($code, $closing_tag, $start_pos + 1);
    $output = array(
        'tag' => $tag
    );
    if($end_pos === false) {
        $output['self_closing'] = true;
        $end_pos = (substr($code, -2) == '/>') ? -2 : -1;
        $code = substr($code, $start_pos + 1, $end_pos);
    } else {
        $output['self_closing'] = false;
        $code = substr($code, $start_pos + 1, $end_pos - $start_pos - 1);
    }
    for($i = 0; $i < strlen($code) ; $i ++) {
        $char = $code[$i];
        if($char == $SINGLE_QUOTE || $char == $DOUBLE_QUOTE) {
            if(empty($quote_type)) {
                $quote_encoded = true;
                $quote_type = $char;
            } else if ($quote_type == $char) {
                if(!empty($cache)) {
                    $string = implode('', $cache);
                    if(empty($var_name)) {
                        $var_name = $string;
                    } else if($var_assign) {
                        $output[$var_name] = $string;
                        unset($var_name);
                    }
                }
                $quote_type = '';
                $var_assign = false;
                $cache = array();
                $quote_encoded = false;
            } else {
                array_push($cache, $char);
            }
        } else if ( !$quote_encoded && $char == ' ' ) {
            if(!empty($cache)) {
                $string = implode('', $cache);
                if(empty($var_name)) {
                    $var_name = $string;
                } else if($var_assign) {
                    $output[$var_name] = $string;
                    unset($var_name);
                }
                $quote_encoded = false;
                $var_assign = false;
                $cache = array();
            }
        } else if ( !$quote_encoded && $char == $ASSIGN_SIGN ) {
            if(!empty($var_name)) {
                $output[$var_name] = '';
            }
            $string = implode('', $cache);
            $var_name = $string;
            $var_assign = true;
            $cache = array();
        } else if ( !$quote_encoded && $char == $TAG_END ) {
            break;
        } else {
            array_push($cache, $char);
        }
    }
    if($output['self_closing'] === false) {
        $output['container'] = substr($code, $i + 1);
    }
    return $output;
}
