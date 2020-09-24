<?php
/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Roulette\Ux;

use Roulette\Model;
use Roulette\Collection;

/**
 * Collection aka Store for extjs compatibility
 * 
 * @package \Roulette\Ux
 * @since Version 2.0.0
 * @author Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 */
class ExtModel extends Model
{
	function find( $condition = array(), $order = null, $group = null, $having = null )
	{
        // sort init
        if( !empty($order) )
        {
            $sort = $order;
            if( is_string($sort) )
            {
                $sort = json_decode($sort);
            }
            if(is_array($sort))
            {
                // sorter from ext params are different with Roulette standart
                // so we need to parse it
                $newSort = array();
                foreach ($sort as $key => $order)
                {
                    if(is_object($order)) $order = (array) $order;
                    if(is_array($order))
                    {
                        $sortKey = null;
                        $sortDir = 'ASC';
                        if(array_key_exists('property', $order)) $sortKey = $order['property'];
                        if(array_key_exists('field', $order)) $sortKey = $order['field'];
                        if(array_key_exists('direction', $order)) $sortDir = $order['direction'];
                        
                        if(!empty($sortKey))
                        {
                            $newSort[$sortKey] = $sortDir;
                        }
                    }
                }
            }

            $order = $sort;
        }

        if(!empty($condition))
        {
            $filters = $condition;

            // filters init
            $encoded = false;
            if ( is_string($filters)) {
                $encoded = true;
                $filters = json_decode($filters);
            }
            if (is_array($filters)) {
                $newFilters = array();
                for ($i=0; $i<count($filters); $i++){
                    $filter = $filters[$i];
                    if ($encoded) {
                        $field      = isset($filter->field)         ? $filter->field : $filter->property;
                        $value      = isset($filter->value)         ? $filter->value : '';
                        $compare    = isset($filter->comparison)    ? $filter->comparison : null;
                        $filterType = isset($filter->type)          ? $filter->type : 'string';
                    } else {
                        $field      = isset($filter['field'])               ? $filter['field'] : $filter['property'];
                        $value      = isset($filter['data']['value'])       ? $filter['data']['value'] : '';
                        $compare    = isset($filter['data']['comparison'])  ? $filter['data']['comparison'] : null;
                        $filterType = isset($filter['data']['type'])        ? $filter['data']['type'] : 'string';
                    }
                    switch($filterType)
                    {
                        case 'custom'   : $newFilters[] = $value; break;
                        case 'string'   : $newFilters[$field] = "LIKE '%$value%"; break;
                        case 'boolean'  : $newFilters[$field] = ((boolean)$value ? 1:0); break;
                        case 'exact'    : $newFilters[$field] = empty($value) ? "IS NULL" : $value; break;
                        case 'list' :
                            if(is_array($value))
                            {
                                foreach ($value as $i => $v) 
                                {
                                    if($value[$i]===null) $value[$i]='NULL';
                                }
                                $value = implode(',',$value);
                                $newFilters[$field] = "IN ($value)";
                            }
                            else if (is_string($value) and strstr($value,','))
                            {
                                $fi = explode(',',$value);
                                foreach ($value as $i => $v) {
                                    $fi[$i] = "'$fi[$i]'";
                                }
                                $value = implode(',',$fi);
                                $newFilters[$field] = "IN ($value)";
                            }
                            else
                            {
                                $newFilters[$field] = $value;
                            }
                            break;
                        case 'numeric' :
                            switch ($compare)
                            {
                                case 'eq' : $newFilters[$field] = " = $value"; break;
                                case 'lt' : $newFilters[$field] = " < $value"; break;
                                case 'gt' : $newFilters[$field] = " > $value"; break;
                            }
                            break;
                        case 'date' :
                            switch ($compare)
                            {
                                case 'eq' : $newFilters[$field] = " = '".date('Y-m-d',strtotime($value))."'"; break;
                                case 'lt' : $newFilters[$field] = " < '".date('Y-m-d',strtotime($value))."'"; break;
                                case 'gt' : $newFilters[$field] = " > '".date('Y-m-d',strtotime($value))."'"; break;
                            }
                            break;
                    }
                }
                $filters = $newFilters;
            }

            $condition = $filters;
        }

        parent::find($condition, $order, $limit, $skip);

		return $this;
	}
}