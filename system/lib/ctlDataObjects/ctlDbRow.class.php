<?php
/*
    Copyright 2007,2008,2009 Emilis Dambauskas

    This file is part of ctlDataObjects library.

    ctlDataObjects library is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ctlDataObjects library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ctlDataObjects library.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . '/ctlDataObject.class.php';

/**
 * Implements methods for DataObjects stored in DB rows.
 */
class ctlDbRow extends ctlDataObject
{
    /**
     * DB table name.
     */
    var $tableName;

    /**
     *
     */
    function __construct($obj_name, $obj_config = false)
    {
        parent::__construct($obj_name, $obj_config);

        // tableName:
        if (array_key_exists('tableName', $this->objectConfig))
            $this->tableName = $this->objectConfig['tableName'];
        else if (!$this->tableName)
            // lowercase to avoid compatibility issues with Windows MySQL servers:
            $this->tableName = strtolower($this->guessTableName());
    }

    /**
     * Guesses tableName by objectName.
     * @return string
     */
    private function guessTableName()
    {
        assert(is_string($this->objectName()));
        assert(strlen($this->objectName) > 3); // "M.O" - Module.Object at least.

        // Module.dbItem becomes dbrItems (pluralize yeah):
        $obj_oname = getObjectName($this->objectName);
        
        assert(strlen($obj_oname) > 0);
    
        $lastchar = substr($obj_oname, -1);
        
        switch ($lastchar)
        {
            case 's':
            case 'x':
                return $obj_oname.'es';
                break;
            case 'y':
                return substr($obj_oname,0,-1).'ies';
                break;
            case 'h':
                $last2chars  = substr($obj_oname, -2);
                if ($last2chars == 'ch' || $last2chars == 'sh')
                    return $obj_oname.'es';
                // Note no "break;".
            default:
                if (is_numeric($lastchar))
                    return $obj_oname;
                else
                    return $obj_oname.'s';
        }
    }
    

    /**
     * Returns DB object.
     * @return object
     */
    function &getDb()
    {
        return loadObject('DB');
    }


    /**
     * Gets DB table field names (caches them both as static var and in SimpleCache).
     * @return array
     */
    function getFieldNames()
    {
        static $fields;
        if ($fields)
            return $fields;
        
        $cache = &loadObject('SimpleCache');
        
        if ($fields = $cache->get("$this->objectName:getFieldNames"))
        {
            assert(is_array($fields));
            assert(count($fields));
            return $fields;
        }
        else if (($fields = parent::getFieldNames()) && is_array($fields) && count($fields))
        {
            assert(is_array($fields));
            assert(count($fields));
            return $fields;
        }
        else
        {
            $db = &$this->getDb();
            
            $sql = "SELECT * FROM `$this->tableName`";
            
            $fields = array();
            
            if ($rs = $db->SelectLimit($sql, 1))
            {
                $fieldCount = $rs->FieldCount();
                for ($i=0;$i<$fieldCount; $i++)
                {
                    $field = $rs->FetchField($i);
                    $fields[] = $field->name;
                }
                
                $rs->Close();
            }

            assert(is_array($fields));
            assert(count($fields));
            
            $cache->set("$this->objectName:getFieldNames", $fields);
            return $fields;
        }

    }


    /**
     * Escapes special characters in fields to avoid SQL injection.
     * @return string
     */
    function quoteValue($field, $value)
    {
        return $this->getDb()->Quote($value);
    }


    /**
     * Checks if current object exists in Database.
     * @return int Returns how many rows with the same ID exist.
     */
    function exists()
    {
        if (empty($this->id))
            return 0;
        else
        {
            $sql = "SELECT count(*) FROM `$this->tableName` WHERE id=" . $this->quoteValue('id', $this->id);
            return intval($this->getDb()->GetOne($sql));
        }
    }


    /**
     * Writes new object into database.
     * @return mixed ResultSet object on success, false on failure.
     * @todo: Check what really is returned from ADOdb/ADOdbLite and update documentation.
     */
    function insert()
    {
        $fields = $this->getFieldNames();
        $sql = "INSERT INTO `$this->tableName` (`" . implode('`,`', $fields) . "`) values(";

        if (empty($this->id))
            $this->id = $this->getDb()->GenID();

        $separator = '';
        foreach ($fields as $field)
        {
            $sql .= $separator . $this->quoteValue($field, @$this->$field);
            $separator = ',';
        }
        $sql .= ")";

        return $this->getDb()->Execute($sql);
    }


    /**
     * Updates object row in database.
     * @return mixed ResultSet object on success, false on failure.
     * @todo: Check what really is returned from ADOdb/ADOdbLite and update documentation.
     */
    function update()
    {
        $fields = $this->getFieldNames();
        $sql = "UPDATE `$this->tableName` SET ";
        
        $separator = '';
        foreach ($fields as $field)
        {
            $sql .= $separator . "`$field`=" . $this->quoteValue($field, $this->$field);
            $separator = ',';
        }

        $sql .= " WHERE id=" . $this->quoteValue('id', $this->id);

        return $this->getDb()->Execute($sql);
    }

    
    /**
     * Saves object information to DB row.
     * @return mixed Whatever insert() or update() return.
     * @todo: Implement checking how many rows exist.
     */
    function save()
    {
        if ($this->exists())
            return $this->update();
        else
            return $this->insert();
    }


    /**
     * Deletes DB table row associated with the object.
     * @return mixed ResultSet object on success, false on failure.
     * @todo: Check what really is returned from ADOdb/ADOdbLite and update documentation.
     */
    function delete()
    {
        assert(!empty($this->id));

        $sql = "DELETE FROM `$this->tableName` WHERE id=" . $this->quoteValue('id', $this->id);
        return $this->getDb()->Execute($sql);
    }


    /**
     * Reads information from DB table row and updates object fields.
     * @return boolean TRUE on success, FALSE on failure.
     */
    function read($id)
    {
        assert(!empty($id));
        
        $sql = "SELECT * FROM `$this->tableName` WHERE id=" . $this->quoteValue('id', $id);
        $data = $this->getDb()->GetRow($sql);
        $this->assign($data);

        return !empty($data);
    }
}
