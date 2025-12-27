<?php

interface BaseRepository {

    public function findAll();
    public function findById($id);
    public function create($object);
    public function update($id);
    public function delete($id);

}