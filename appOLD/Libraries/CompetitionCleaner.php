<?php

namespace App\Libraries;

use Config\Database;

class CompetitionCleaner
{

    public function deleteCompetition($id)
    {
        $db = Database::connect();

        log_message('debug', 'DELETE COMPETITION ' . $id);


        /*
        ----------------------
        DELETE DB
        ----------------------
        */

        $db->table('notes')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('photos')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('juges')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('medailles')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('classements')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('competitions')
            ->where('id', $id)
            ->delete();


        /*
        ----------------------
        DELETE FILES
        ----------------------
        */

        $folder =
            $this->getCompetitionFolder($id);

        $path =
            FCPATH .
            'uploads/competitions/' .
            $folder;

        if (is_dir($path)) {
            $this->deleteDir($path);
        }


        /*
        ----------------------
        DELETE IMPORT TMP
        ----------------------
        */

        $tmp =
            WRITEPATH .
            "imports/tmp_$id";

        if (is_dir($tmp)) {
            $this->deleteDir($tmp);
        }

        $zip =
            WRITEPATH .
            "imports/$id.zip";

        if (file_exists($zip)) {
            unlink($zip);
        }
    }


    /*
    ===========================
    GET FOLDER NAME
    ===========================
    */

    private function getCompetitionFolder($id)
    {
        $db = Database::connect();

        $c =
            $db->table('competitions')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$c) {
            return $id;
        }

        return
            $c['saison']
            . '_'
            . ($c['urs_id'] ?? 0)
            . '_'
            . ($c['numero'] ?? 0)
            . '_'
            . $id;
    }


    /*
    ===========================
    DELETE DIR
    ===========================
    */

    private function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files =
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $dir,
                    \RecursiveDirectoryIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }
}
