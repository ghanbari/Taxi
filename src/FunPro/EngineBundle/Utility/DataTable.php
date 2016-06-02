<?php

namespace FunPro\EngineBundle\Utility;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;

class DataTable
{
    public static function orderBy(QueryBuilder $queryBuilder, Request $request)
    {
        $columns = $request->query->get('columns');
        $orders = $request->query->get('order');

        if (is_array($orders) and is_array($columns)) {
            foreach ($orders as $orderBy) {
                if ($columns[$orderBy['column']]['orderable'] !== 'false') {
                    $queryBuilder->addOrderBy($columns[$orderBy['column']]['name'], $orderBy['dir']);
                }
            }
        }
    }

    public static function filterBy(QueryBuilder $queryBuilder, Request $request)
    {
        $columns = $request->query->get('columns');
        $search = $request->query->get('search');
        $i = 1;

        if (is_array($columns)) {
            foreach ($columns as $column) {
                if ($column['searchable'] !== 'false' and !empty($column['name'])) {
                    if (!empty($search['value'])) {
                        $queryBuilder->orWhere($queryBuilder->expr()->like($column['name'], ":value$i"))
                            ->setParameter("value$i", '%'.$search['value'].'%');
                    }

                    if (!empty($column['search']['value'])) {
                        $queryBuilder->orWhere($queryBuilder->expr()->like($column['name'], "value-$i"))
                            ->setParameter("value-$i", '%'.$column['search']['value'].'%');
                    }
                }
                $i++;
            }
        }
    }
}