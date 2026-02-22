<?php

namespace PhpCrud;

use PhpCrud\Model;

abstract class DB_Model extends Model
{
    public static function __getPrimaryKey()
    {
        $class = get_called_class();
        return (defined($class::primary_key)) ?? false;
    }

    protected static function __processHooks(&$data)
    {
        $class = get_called_class();
        foreach($data as $field => $value) {
            if(method_exists($class, 'hook_'.$field)) {
                $data[$field] = $class::{'hook_'.$field}($value);
            }
        }
    }

    public static function __buildSelectAll()
    {
        $class = get_called_class();
        $sql = sprintf('SELECT %s, %s FROM %s', join(',', $class::keys), join(',', $class::attrs), $class::table);
        return $sql;
    }
    public static function __buildSelect($id)
    {
        $class = get_called_class();
        if (is_numeric($id)) {
            $sql = sprintf('SELECT %s, %s FROM %s WHERE %s=%d', join(',', $class::keys), join(',', $class::attrs), $class::table, $class::primary_key, $id);
        } else {
            $sql = sprintf("SELECT %s, %s FROM %s WHERE %s='%s'", join(',', $class::keys), join(',', $class::attrs), $class::table, $class::primary_key, addslashes($id));
        }
        return $sql;
    }

    public static function __buildSelectWhere($col, $val)
    {
        $class = get_called_class();
        $sql = '';
        if(is_string($col) && is_string($val)) {
            $sql = sprintf('SELECT %s, %s FROM %s WHERE %s=%s', join(',', $class::keys), join(',', $class::attrs), $class::table, $col, $val);
        }
        if((is_array($col) && is_array($val))  && (count($col) == count($val))) {
            $sql = sprintf('SELECT %s, %s FROM %s WHERE ', join(',', $class::keys), join(',', $class::attrs), $class::table);
            foreach($col as $i => $name) {
                $sql .= sprintf(" %s=%s AND", $name, $val[$i]);
            }
            $sql = rtrim($sql, " AND");
        }
        return $sql;
    }

    public static function __buildInsert($data)
    {
        $class = get_called_class();
        self::__processHooks($data);
        $sql = sprintf('INSERT INTO %s SET ', $class::table);

        $schema = (defined("$class::schema")) ? $class::schema : [];
        foreach($class::attrs as $i => $key) {
            if(isset($data[$key])) {
                if(in_array($key, array_keys($schema))) {
                    if(is_null($data[$key])) {
                        $sql .= sprintf("%s=NULL, ", $key);
                    } else {
                        switch($schema[$key]) {
                            case 'int':
                                if(!empty($data[$key])) {
                                    $sql .= sprintf("%s=%d, ", $key, $data[$key]);
                                }
                                break;
                            default:
                                $sql .= sprintf("%s='%s', ", $key, addslashes($data[$key]));
                        }
                    }
                } else {
                    $sql .= sprintf('%s="%s", ', $key, addslashes($data[$key]));
                }
            }
        }
        $sql = rtrim($sql, ', ');
        return $sql;
    }

    public static function __buildUpdate($id, $data)
    {
        $class = get_called_class();
        self::__processHooks($data);
        $updates = 0;
        $sql = sprintf('UPDATE %s SET ', $class::table);

        $restricted = (defined("$class::restricted")) ? $class::restricted : [];
        $schema = (defined("$class::schema")) ? $class::schema : [];
        foreach($class::attrs as $i => $key) {
            // Prevent updates to the primary key field
            if(isset($data[$key]) && !in_array($key, $restricted) && $key !== $class::primary_key) {
                if(in_array($key, array_keys($schema))) {
                    if(is_null($data[$key])) {
                        $sql .= sprintf("%s=NULL, ", $key);
                    } else {
                        switch($schema[$key]) {
                            case 'int':
                                if(!empty($data[$key])) {
                                    $sql .= sprintf("%s=%d, ", $key, $data[$key]);
                                }
                                break;
                            default:
                                $sql .= sprintf("%s='%s', ", $key, addslashes($data[$key]));
                        }
                    }
                } else {
                    $sql .= sprintf("%s='%s', ", $key, addslashes($data[$key]));
                }

                $updates++;
            }
        }
        $sql = rtrim($sql, ', ');
        if(in_array('last_updated', $class::attrs) || in_array('last_updated', $class::restricted)) {
            $sql .= ", last_updated=NOW()";
        }

        // Support both integer and string primary keys
        if (is_numeric($id)) {
            $sql .= sprintf(' WHERE %s=%d', $class::primary_key, $id);
        } else {
            $sql .= sprintf(" WHERE %s='%s'", $class::primary_key, addslashes($id));
        }

        return ($updates > 0) ? $sql : false;
    }


    public static function __buildDelete($id)
    {
        $class = get_called_class();
        if (is_numeric($id)) {
            $sql = sprintf('DELETE FROM %s WHERE %s=%d', $class::table, $class::primary_key, $id);
        } else {
            $sql = sprintf("DELETE FROM %s WHERE %s='%s'", $class::table, $class::primary_key, addslashes($id));
        }
        return $sql;
    }
}
