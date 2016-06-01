<?php

class CSVFilter
{
    function __construct()
    {
        $this->phrase = "";
        $this->file_names = [];
    }

    private function parseCommandLineArgs($args)
    {
        $this->phrase = $args[1];

        $this->file_names = array_slice($args, 2);
    }

    private function filterInvalidFileNames()
    {
        $filtered_files = [];

        foreach ($this->file_names as $file) {
            if (preg_match('/\.csv$/', $file)) {
                array_push($filtered_files, $file);
            }
        }

        $this->file_names = $filtered_files;
    }

    private function fetchFile($file_name)
    {
        $file = fopen($file_name, 'r');

        return $file;
    }

    private function filterFile($file_name, $skip_header=false)
    {
        $data = "";
        $file = $this->fetchFile($file_name);

        if (!$file) {
            error_log("$file_name does not exist.\n");

            return false;
        }

        if ($skip_header == true) {
            $header = fgets($file);

            $data .= $header;
        }

        while ($row = fgets($file)) {
            if (preg_match('/'.$this->phrase.'/', $row)) {
                continue;
            }

            $data .= $row;
        }

        fclose($file);

        return $data;
    }

    public function filter($args)
    {
        try {
            $this->parseCommandLineArgs($args);
            $this->filterInvalidFileNames();

            foreach ($this->file_names as $file_name) {
                $data = $this->filterFile($file_name);
                file_put_contents($file_name, $data);
            }
        }
        catch (Exception $e) {
            error_log($e->getMessage());
            error_log("Exception occured. Aborting...");
        }
    }

}


$csvfilter = new CSVFilter;
$csvfilter->filter($argv);

?>
