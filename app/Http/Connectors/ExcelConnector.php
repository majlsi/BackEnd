<?php

namespace Connectors;

use Excel;
/**
 * Low-level client for Excel
 *
 * @author eman.mohamed
 */
class ExcelConnector
{
 
    public function __construct()
    {
    }

    /**
     *
     * Read Excel data
     *
     * @param file $file
     *
     * @return Array
     */
    public function readFile($file)
    {

       $destinationPath = storage_path('exports');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        if ($file->move($destinationPath, $filename)) {

            $results=Excel::load(join(DIRECTORY_SEPARATOR, array($destinationPath, $filename)), function($reader)  {
                $reader->noHeading();
            },null,TRUE)->get();

            return $this->prepareObject($results);
        }
        return null;
    }

    private function prepareObject($results)
    {
        $mapedData = [];
        for ($i = 1; $i < count($results); $i++) {
            for ($j = 0; $j < count($results[0]); $j++) {
                if ($results[$i][$j] != null) {
                    $obj[$results[0][$j]] = $results[$i][$j];
                }
            }
            $mapedData[] = $obj;
        }
        return $mapedData;
    }
}