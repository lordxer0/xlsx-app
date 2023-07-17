<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CombinadorController extends Controller
{
    //
    // Genera el mezcla en formato de forma y las cada
    public function generarMezcla(Request $request){
        
        $data = $request->all();
        
        $archivo_origen = $data['archivo_origen'];
        $archivo_destino = $data['archivo_destino'];

        $nombre_destino = $archivo_destino->getClientOriginalName();
        
        $spreadsheet_origen = IOFactory::load($archivo_origen);
        $spreadsheet_destino = IOFactory::load($archivo_destino);


        $datos_origen = $spreadsheet_origen->getActiveSheet()->toArray();
        $datos_destino = $spreadsheet_destino->getActiveSheet()->toArray();

        $arreglo_datos = [];

        foreach ($datos_origen as $key => $value) {
            # code...
            $arreglo_datos[$value[0]] = $value[1];
        }
        
        foreach ($datos_destino as $key => &$value) {
            # code...
            $value[1] = $arreglo_datos[$value[0]];
            $value[0] = '\''.$value[0];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($datos_destino, NULL, 'A1');     

        // redirect output to client browser
        header('Content-Disposition: attachment;filename="nuevo_'.$nombre_destino.'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

    }
}
