
$tempo_em_segundos  = null;
    $tempo_em_horas = null;
    $tempo_em_minutos = null;

    if($NomeAtendido->timestamp)
            {
                $tempo_em_segundos = strtotime(now()) - $NomeAtendido->timestamp;
                            $tempo_em_horas = $tempo_em_segundos / 3600;
                            $tempo_em_minutos = $tempo_em_segundos / 60;
            }


    $numero = $tempo_em_horas;

    $partes = explode('.', $numero);

   
    $parte_inteira = (int)$partes[0];
    $parte_decimal = isset($partes[1]) ? (float)('0.' . $partes[1]) : 0;

    $parte_decimal_minutos = round($parte_decimal * 60);