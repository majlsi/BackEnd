<?php
/*
Base Repositoy
 */

namespace Repositories;
use Prettus\Repository\Eloquent\BaseRepository as Repository;
/**
 * Base to All Repositories that contains common functionalities
 *
 * @author yasser.mohamed
 */
abstract class BaseRepository extends Repository
{

    public function getPagedResults($pageNumber, $pageSize, $withExpressions = array(), Criteria $filter = null, $sortBy = "id", $sortDirection = "ASC", $columns = array('*'))
    {
        //Sort
        $this->model = $this->model->orderBy($sortBy, $sortDirection);
        //Criteria
        if (!is_null($filter)) {
            $this->pushCriteria($filter);
            $this->applyCriteria();
        }

        //Include the related entities
        if (!is_null($withExpressions)) {
            foreach ($withExpressions as $relation) {
                $this->model->with($relation);
            }
        }
        //Pagination
        $count = count($this->model->get($columns));
        $skip = ($pageNumber - 1) * $pageSize;
        $this->model->skip($skip)->take($pageSize);
        $args = array("TotalRecords" => $count, "Results" => $this->model->get($columns));
        return (object) $args;
    }

    public function getPagedQueryResults($pageNumber, $pageSize, $query = null, $sortBy = "id", $sortDirection = "ASC")
    {
        //Sort
        if (is_array($sortBy)) {
            foreach ($sortBy as $key => $value) {
                $query = $query->orderByRaw($value . ' ' . $sortDirection[$key]);
            }
        } else {
            $query = $query->orderBy($sortBy, $sortDirection);
        }
        //Pagination
        $count = count($query->get());
        $skip = ($pageNumber - 1) * $pageSize;
        $query = $query->skip($skip)->take($pageSize);

        $args = array("TotalRecords" => $count, "Results" => $query->get());
        return (object) $args;
    }

    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->find($id)->update($data);
    }
}
