<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Services;

use \Exception;
use Illuminate\Database\DatabaseManager;
use Prettus\Repository\Eloquent\BaseRepository as Repository;
use \Illuminate\Database\Eloquent\Model;
use Repositories\BaseRepository;

/**
 * Base Service
 * @author yasser.mohamed
 */
abstract class BaseService
{
    /**
     *
     * @var DatabaseManager
     */
    private $database;

    /**
     *
     * @param DatabaseManager $database
     */
    public function setDatabase(DatabaseManager $database)
    {
        $this->database = $database;
    }
    /**
     *
     * @var Repository
     */
    protected $repository;

    /**
     *
     * @param Repository $repository
     */
    public function setRepository(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * Abstact Function to be called before creating new object
     * And will be implemented by the repository Service calss
     *
     */
    abstract public function prepareCreate(array $data);

    /**
     * Abstact Function to be called before Updating object
     * And will be implemented by the repository Service calss
     */
    abstract public function prepareUpdate(Model $model,array $data);

    /**
     *
     * Abstact Function to be called before deleting object
     * And will be implemented by the repository Service calss
     *
     */
    abstract public function prepareDelete(int $id);

    /**
     * Return All records of this model
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * Return a specific model with the associate id
     *
     * @param int $id
     * @param array $columns selected columns
     * @return mixed
     */
    public function getById(int $id,$columns = array('*'))
    {
        $model = $this->repository->find($id,$columns);

        return $model;
    }

    /**
     * Create a new instance and save it to DB
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function create(array $data)
    {
        $this->database->beginTransaction();

        try {
            $model = $this->prepareCreate($data);
        } catch (Exception $e) {
            $this->database->rollBack();
            throw $e;
        }

        $this->database->commit();

        return $model;
    }

    /**
     * Update specific record with the associated id
     *
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function update(int $id, array $data)
    {
        $model = $this->repository->find($id);

        $this->database->beginTransaction();

        try {
            $this->prepareUpdate($model, $data);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();

        return $model;
    }

    /**
     * Delete specific record with the associated id
     *
     * @param int $id
     * @throws Exception
     */
    public function delete(int $id)
    {
        $this->database->beginTransaction();

        try {
            $this->prepareDelete($id);
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }

     /**
     * Return All records of this model
     *
     * @return mixed
     */
    public function getPagedResults($pageNumber, $pageSize, $withExpressions = array(), Criteria $filter = null, $sortBy = "id", $sortDirection = "ASC")
    {
        return $this->repository->getPagedResults($pageNumber, $pageSize, $withExpressions,$filter,$sortBy,$sortDirection);
    }

    public function openTransaction(){
        $this->database->beginTransaction();
    }

    public function closeTransaction(){
        $this->database->commit();
    }

    public function rollBack(){
        $this->database->rollBack();
    }
}