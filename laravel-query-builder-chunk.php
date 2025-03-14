<!-- USAR CHUNK DE LARAVEL -->
<?php

try {

    DB::beginTransaction();

    $periodoActual = Carbon::now()->format('Y-m-d H:i:s');
    $periodoMenos5Meses = Carbon::now()->subMonths(3)->firstOfMonth()->startOfDay()->format('Y-m-d H:i:s');


    // Llamada a la función para eliminar registros antes de la inserción
    $this->eliminarRegistrosPorPeriodo($periodoMenos5Meses, $periodoActual);

    // return [$fechaActual,$fechaMenos5Meses];

    $comisiones = DB::connection("cubo_momo")
        ->table('COMISIONES.dbo.comision_cobranzas_miniterminal_histo as cm')
        ->select(
            'cm.id',
            'cm.nombre_terminal',
            'cm.ruc',
            'cm.atm_id',
            'cm.numcompra',
            'cm.anomes as periodo',
            'cm.comision',
            DB::raw("FORMAT(cm.fechacompra, 'yyyy-MM-dd') as fechacompra"),
            'cm.monto as monto_transaccionado',
            'cm.monto_comision',
            'cm.acumulado',
            'cm.descuento',
            'cm.retencion',
            'cm.totalcompra as monto_facturado'
        )
        ->whereBetween('cm.fecha', [$periodoMenos5Meses, $periodoActual]);

    $comisiones->chunk(50000, function ($results) {
        $insertData = [];
        $uniqueData = [];

        foreach ($results as $result) {
            $fechaString = $result->periodo;
            $fechaCarbon = Carbon::createFromFormat('Ym', $fechaString);
            $fechaFormateada = $fechaCarbon->format('Y-m');

            $insertData[] = [
                'transaction_id' => $result->id,
                'atm_id' => $result->atm_id,
                'ruc' => $result->ruc,
                'periodo' => $fechaFormateada,
                'porcentaje_comision' => $result->comision,
                'numero_factura' => $result->numcompra,
                'fecha_pago' => $result->fechacompra,
                'monto_transaccionado' => $result->monto_transaccionado,
                'monto_comision' => $result->monto_comision,
                'monto_acumulado' => $result->acumulado,
                'monto_descuento' => $result->descuento,
                'monto_retencion' => $result->retencion,
                'monto_facturado' => $result->monto_facturado,
            ];

            $uniqueData[] = $result->ruc;
        }

        $grupos = DB::table('business_groups')->whereIn('ruc', $uniqueData)->get();

        foreach ($insertData as &$result) {
            $gruporesult = null;

            // Buscar el grupo correspondiente manualmente
            foreach ($grupos as $grupo) {
                if ($grupo->ruc == $result['ruc']) {
                    $gruporesult = $grupo;
                    break;
                }
            }

            // Asignar el group_id basado en el resultado de la búsqueda
            $result['group_id'] = $gruporesult ? $gruporesult->id : null;

            // Eliminar la clave 'ruc' del arreglo $result
            unset($result['ruc']);
        }

        \Log::info('Inserting batch of ' . count($insertData) . ' records.');

        // Dividir la inserción en lotes más pequeños (por ejemplo, 1000 registros por lote)
        $chunks = array_chunk($insertData, 5000);

        foreach ($chunks as $chunk) {
            DB::table('commission.liquidated_invoice_ondanet')->insert($chunk);
        }
    });

    DB::commit();
} catch (Exception $e) {
    \Log::error($e->getMessage());
    DB::rollBack();
}
