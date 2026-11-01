<?php

require_once __DIR__ . '/../model/kelas.php';

class KelasController
{
    public function index(): void
    {
        $model = new Kelas();
        $kelas = $model->getAll();
        $selectedId = filter_var(
            $_GET['kelas_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $selectedKelas = null;

        if ($selectedId !== false && $selectedId !== null) {
            foreach ($kelas as $item) {
                if ((int)$item['id'] === $selectedId) {
                    $selectedKelas = $item;
                    break;
                }
            }
        }

        renderView('kelas/index', [
            'kelas' => $kelas,
            'selectedKelas' => $selectedKelas,
            'selectedKelasId' => $selectedKelas['id'] ?? '',
        ]);
    }
}