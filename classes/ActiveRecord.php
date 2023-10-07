<?php

namespace App;

class ActiveRecord {

    // DB
    protected static $db;
    protected static $columnsDB = [];
    protected static $table = '';
    
    // Errors
    protected static $errors = [];


    public static function setDB($database) {
        self::$db = $database;
    }
    
    public function save() {
        if(!is_null($this->id)) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function create() {
        // Insert data sanitize
        $attributes = $this->sanitizeData();

        // DB Insert
        $query = "INSERT INTO " . static::$table . " ( ";
        $query .= join(', ', array_keys($attributes));
        $query .= " ) VALUES ('";
        $query .= join("', '", array_values($attributes));
        $query .= " ')";
        
        $result = self::$db->query($query);

        // Error message
        if($result) {
            // Redirect User
            header('Location: ../index.php?result=1');
        }
    }

    public function update() {
        // Insert data sanitize
        $attributes = $this->sanitizeData();

        $values = [];
        foreach($attributes as $key => $value) {
            $values[] = "{$key}='{$value}'";
        }

        $query = "UPDATE " . static::$table . " SET ";
        $query .= join(', ', $values );
        $query .= " WHERE id = '" . self::$db->escape_string($this->id) . "' ";
        $query .= " LIMIT 1";

        $result = self::$db->query($query);

        if($result) {
            // Redirect User
            header('Location: ../index.php?result=2');
        }
    }

    public function delete() {
        $query = "DELETE FROM " . static::$table . " WHERE id = " . self::$db->escape_string($this->id) . " LIMIT 1";
        $result = self::$db->query($query);

        if($result) {
            $this->deleteImage();
            header('location: ../admin/index.php?result=3');
        }
    }

    public function attributes() {
        $attributes = [];
        foreach(self::$columnsDB as $column) {
            if($column === 'id') continue;
            $attributes[$column] = $this->$column;
        }
        return $attributes;
    }

    public function sanitizeData() {
        $attributes = $this->attributes();
        $sanitized = [];
        
        foreach($attributes as $key => $value) {
            $sanitized[$key] = self::$db->escape_string($value);
        }
        
        return $sanitized;
    }

    // File upload
    public function setImage($image) {
        // Delete previous image
        if(!is_null($this->id)) {
            $this->deleteImage();
        }

        // Asign image's attribute name's image
        if($image) {
            $this->image = $image;
        }
    }

    // File delete
    public function deleteImage() {
        $existFile = file_exists(IMAGES_FOLDER . $this->image);
        if($existFile) {
            unlink(IMAGES_FOLDER . $this->image);
        }
    }

    // Validation
    public static function getErrors() {
        return self::$errors;
    }

    public function validate() {

        if(!$this->title) {
            self::$errors[] = "Debes añadir un título";
        }

        if(!$this->price) {
            self::$errors[] = "Debes añadir un precio";
        }

        if(strlen($this->description) < 50){
            self::$errors[] = "La descripción es obligatoria y debe tener al menos 50 caracteres";
        }

        if(!$this->bedrooms) {
            self::$errors[] = "El número de habitaciones es obligatorio";
        }

        if(!$this->wc) {
            self::$errors[] = "El número de baños es obligatorio";
        }

        if(!$this->parking) {
            self::$errors[] = "El número de plazas de garage es obligatorio";
        }

        if(!$this->sellers_id) {
            self::$errors[] = "Elige un vendedor";
        }

        if(!$this->image) {
            self::$errors[] = "La imagen es obligatoria";
        }

        return self::$errors;
    }

    // All register list
    public static function all() {
        $query = "SELECT * FROM " . static::$table;
        
        $result = self::sqlConsult($query);

        return $result;
    }

    // Search register
    public static function find($id) {
        $query = "SELECT * FROM " . static::$table . " WHERE id = $id";

        $result = self::sqlConsult($query);

        return array_shift($result);
    }

    public static function sqlConsult($query) {
        // DB consult
        $result = self::$db->query($query);

        // Iterate results
        $array = [];
        while($register = $result->fetch_assoc()) {
            $array[] = self::createObject($register);
        }

        // Memory liberation
        $result->free();

        // Results return
        return $array;

    }

    public static function createObject($register) {
        $object = new static;

        foreach($register as $key => $value) {
            if(property_exists($object, $key)) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    // Synchronize
    public function synchronize($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}