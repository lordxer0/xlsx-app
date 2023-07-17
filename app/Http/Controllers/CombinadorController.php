<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CombinadorController extends Controller
{
    //
    /**
     * The function generates a new Excel file by merging data from two existing Excel files.
     * 
     * @param Request request The  parameter is an instance of the Request class, which is used
     * to handle HTTP requests in Laravel. It contains information about the current request, such as
     * the request method, headers, and input data.
     */
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
            if($key != 0 ){
                $arreglo_datos[$value[0]] = $value[1];
            }
        }
        
        $nombre_log = 'log_comprobacion_'.date('Y-m-d').'.txt';

        $log = fopen(storage_path('logs/'.$nombre_log),'c+');
        
        foreach ($datos_destino as $key => &$value) {
            # code....
            if($key != 0 ){
                if(isset($arreglo_datos[$value[0]])){
                    
                    $cambio = ($value[1] != $arreglo_datos[$value[0]]) ? 'cambio':'igual';

                    $message =  "codigo ".$value[0]." = antes ".$value[1]." // despues " .$arreglo_datos[$value[0]] ." ". $cambio;
                    $txt = "[" . date('Y-m-d H:i:s') . "]:\t".  ((isset($message)) ? $message : "Error inesperado") . "\n";
                    
                    $value[1] = $arreglo_datos[$value[0]];
                    $value[0] = $value[0];
                }else {
                    $message =  "codigo ".$value[0]." faltante en el segundo archivo ";
                    $txt = "[" . date('Y-m-d H:i:s') . "]:\t".  ((isset($message)) ? $message : "Error inesperado") . "\n";
                }
                fwrite($log, $txt);
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($datos_destino, NULL, 'A1');     

        fclose($log);
                // redirect output to client browser
        header('Content-Disposition: attachment;filename="nuevo_'.explode('.',$nombre_destino)[0].'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

    }
}
