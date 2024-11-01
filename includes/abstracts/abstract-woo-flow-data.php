<?php

/**
 * Abstract class which has helper functions to get data from the database
 */
abstract class Woo_Flow_Data_Helper {

    /**
     * Constructor for the database class to inject the table name
     */
    public function __construct() {

    }

    /**
     * Insert data into the current data
     *
     * @param  String $table_name - Name of table will be get data
     * @param  array $data - Data to enter into the database table
     *
     * @return int Insert Query Object
     */
    public function insert($table_name, array $data) {
        global $wpdb;

        if ( empty($data) ) {
            return false;
        }

        $wpdb->insert($table_name, $data);

        return $wpdb->insert_id;
    }

    /**
     * Get all from the selected table
     *
     * @param  String $table_name - Name of table will be get data
     * @param  String $order_by - Order by column name
     *
     * @return array Table result
     */
    public function get_all( $table_name, $order_by = NULL ) {
        global $wpdb;

        $sql = 'SELECT * FROM `' . $table_name . '`';

        if (!empty($order_by)) {
            $sql .= ' ORDER BY ' . $order_by;
        }
        $all = $wpdb->get_results($sql);

        return $all;
    }

    /**
     * Get a value by a condition
     *
     * @param  String $table_name - Name of table will be get data
     * @param  Array $conditionValue - A key value pair of the conditions you want to search on
     * @param  String $condition - A string value for the condition of the query default to equals
     *
     * @return array Table result
     */

    /**
     * Get a value by a condition
     *
     * @param $table_name - Name of table will be get data
     * @param array $condition_value - A key value pair of the conditions you want to search on
     * @param string $condition - A string value for the condition of the query default to equals
     * @return array Table result
     * @throws Exception
     */
    public function get_by( $table_name, array $condition_value, $condition = '=') {
        global $wpdb;

        $sql = 'SELECT * FROM `' . $table_name . '` WHERE ';

        $count = 0;
        foreach ($condition_value as $field => $value) {
            $count++;
            switch (strtolower($condition)) {
                case 'in':
                    if (!is_array($value)) {
                        throw new Exception("Values for IN query must be an array.", 1);
                    }
                    $sql .= $wpdb->prepare('`%s` IN (%s)', $field, implode(',', $value));
                    break;
                default:
                    if (count($condition_value) == $count) {
                        $sql .= $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value);
                    } else {
                        $sql .= $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value) . ' AND ';
                    }

                    break;
            }
        }
        $result = $wpdb->get_results($sql);
        //var_dump($sql);

        return $result;
    }

    /**
     * Get all from the selected table
     *
     * @param  String $table_name - Name of table will be get data
     * @param  array $select - Array data will give to select
     * @param  array $conditions - Array of condition list
     * @param  String $order_by - Order by column name
     * @param  array $group_by - Array group by column
     * @param  int $limit - Limit number
     *
     * @throws
     * @return array Table result
     */
    public function get_records( $table_name, $select, array $conditions = NULL, $order_by = NULL,
                                 array $group_by  = NULL, $limit = NULL ) {
        global $wpdb;

        $sql = 'SELECT ';

        $count = 0;
        if (!empty($select)) {
            foreach ($select as $key => $value) {
                $count++;
                if (count($select) == $count) {
                    switch ($value) {
                        case '':
                            $sql .= $key;
                            break;
                        default:
                            $sql .= $key . ' AS ' . $value;
                            break;
                    }
                } else {
                    switch ($key) {
                        case '*':
                            $sql .= $key .', ';
                            break;
                        default:
                            $sql .= $key . ' AS ' . $value .', ';
                            break;
                    }
                }
            }
        } else {
            $sql .= '* ';
        }

        $sql .= ' FROM `' . $table_name . '`';

        if (!empty($conditions)) {
            $sql .= ' WHERE ';
            $pos = 0;
            foreach ($conditions as $condition => $values_array ) {
                $pos++;
                $count = 0;
                foreach ($values_array as $field => $value) {
                    $count++;
                    if ( (count($values_array) == $count) && (count($conditions) == $pos) ) {
                        switch ($condition) {
                            case 'datetime':
                                $sql .= 'STR_TO_DATE(`' . $value[0] . '`, "%Y-%m-%d") ' . $field . ' STR_TO_DATE("'. $value[1] .'", "%Y-%m-%d")';
                                break;
                            case 'in':
                                if (!is_array($value)) {
                                    throw new Exception("Values for IN query must be an array.", 1);
                                }
                                $in_array = '';
                                foreach ($value as $key => $in) {
                                    if ($key === 0) {
                                        $in_array .= '"'. $in .'"';
                                    } else {
                                        $in_array .= ', "'. $in .'"';
                                    }
                                }
                                $sql .= '`' . $field . '` IN ('. $in_array .')';
                                break;
                            default:
                                $sql .= $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value);
                                break;
                        }
                    } else {
                        switch ($condition) {
                            case 'datetime':
                                $sql .= 'STR_TO_DATE(`' . $value[0] . '`, "%Y-%m-%d") ' . $field . ' STR_TO_DATE("'. $value[1] .'", "%Y-%m-%d") AND ';
                                break;
                            case 'in':
                                if (!is_array($value)) {
                                    throw new Exception("Values for IN query must be an array.", 1);
                                }
                                $in_array = '';
                                foreach ($value as $key => $in) {
                                    if ($key === 0) {
                                        $in_array .= '"'. $in .'"';
                                    } else {
                                        $in_array .= ', "'. $in .'"';
                                    }
                                }
                                $sql .= '`' . $field . '` IN ('. $in_array .') AND ';
                                break;
                            default:
                                $sql .= $wpdb->prepare('`' . $field . '` ' . $condition . ' %s', $value) . ' AND ';
                                break;
                        }
                    }
                }
            }
        }

        if (!empty($group_by)) {
            $sql .= ' GROUP BY ';
            foreach ($group_by as $value) {
                if ($group_by[sizeof($group_by) - 1] === $value) {
                    $sql .= $value;
                } else {
                    $sql .= $value .', ';
                }
            }
        }

        if (!empty($order_by)) {
            $sql .= ' ORDER BY ' . $order_by;
        }

        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        $all = $wpdb->get_results($sql);
//        var_dump($sql);

        return $all;
    }

    /**
     * Update a table record in the database
     *
     * @param  String $table_name - Name of table will be get data
     * @param  array $data - Array of data to be updated
     * @param  array $condition_value - Key value pair for the where clause of the query
     *
     * @return array|false|int Updated object
     */
    public function update( $table_name, array $data, array $condition_value) {
        global $wpdb;

        if (empty($data)) {

            return false;

        }

        $updated = $wpdb->update($table_name, $data, $condition_value);

        return $updated;
    }

    /**
     * Delete row on the database table
     *
     * @param  String $table_name - Name of table will be get data
     * @param  array $condition_value - Key value pair for the where clause of the query
     *
     * @return Int - Num rows deleted
     */
    public function delete( $table_name, array $condition_value) {

        global $wpdb;

        $deleted = $wpdb->delete($table_name, $condition_value);

        return $deleted;
    }

    public function delete_all( $table_name ) {

        global $wpdb;

        $deleted = $wpdb->query("TRUNCATE TABLE `". $table_name ."`");

        return $deleted;

    }

    public function get_query( $sql ) {
        global $wpdb;

        $result = $wpdb->query($sql);

        return $result;

    }
}