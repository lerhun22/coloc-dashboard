<?php
/*253*/

namespace App\Libraries;

class ImportWorkflow
{
    private string $dir;
    private string $stateFile;


    public function __construct($id)
    {
        $this->dir =
            WRITEPATH . "imports/$id/";

        $this->stateFile =
            $this->dir . "state.json";

        if (!is_dir($this->dir)) {
            mkdir(
                $this->dir,
                0777,
                true
            );
        }
    }


    /*
    ===========================
    GET STATE
    ===========================
    */

    public function getState(): array
    {
        if (!file_exists($this->stateFile)) {

            return [
                'step' => 'init',
                'progress' => 0,
                'status' => 'idle'
            ];
        }

        $data =
            json_decode(
                file_get_contents(
                    $this->stateFile
                ),
                true
            );

        if (!$data) {

            return [
                'step' => 'init',
                'progress' => 0,
                'status' => 'idle'
            ];
        }

        return $data;
    }


    /*
    ===========================
    SAVE
    ===========================
    */

    public function save(array $data)
    {
        file_put_contents(
            $this->stateFile,
            json_encode(
                $data,
                JSON_PRETTY_PRINT
            )
        );
    }


    /*
    ===========================
    UPDATE (fusion)
    ===========================
    */

    public function update(array $data)
    {
        $state =
            $this->getState();

        foreach ($data as $k => $v) {
            $state[$k] = $v;
        }

        $this->save($state);
    }


    /*
    ===========================
    SET STEP
    ===========================
    */

    public function setStep(
        string $step,
        int $progress = null
    ) {

        $state =
            $this->getState();

        $state['step'] =
            $step;

        $state['status'] =
            'running';

        if ($progress !== null) {
            $state['progress'] =
                $progress;
        }

        $this->save($state);
    }


    /*
    ===========================
    UPDATE PROGRESS
    ===========================
    */

    public function progress(int $progress)
    {
        $state =
            $this->getState();

        if ($progress > 100) {
            $progress = 100;
        }

        if ($progress < 0) {
            $progress = 0;
        }

        $state['progress'] =
            $progress;

        $this->save($state);
    }


    /*
    ===========================
    DONE
    ===========================
    */

    public function done()
    {
        $state =
            $this->getState();

        $state['step'] =
            'done';

        $state['progress'] =
            100;

        $state['status'] =
            'done';

        $this->save($state);
    }


    /*
    ===========================
    ERROR
    ===========================
    */

    public function error($msg = '')
    {
        $state =
            $this->getState();

        $state['step'] =
            'error';

        $state['status'] =
            $msg;

        $this->save($state);
    }
}