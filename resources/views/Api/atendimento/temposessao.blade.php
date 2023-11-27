@if ($tempo_em_segundos != null)
        {{-- Este momento: {{ strtotime(now()) }} --}}
        <nav class="navbar navbar-red" style="background-color: hsla(234, 92%, 47%, 0.096);">
            Tempo de sessão:
            @if ($parte_inteira >= 1)
                {{ $parte_inteira }} hora e
            @else
                @if ($parte_inteira > 1)
                    {{ $parte_inteira }} horas e
                @endif
            @endif


            @if ($parte_decimal_minutos == 1)
                {{ $parte_decimal_minutos }} minuto
            @else
                @if ($parte_decimal_minutos >= 2)
                    {{ $parte_decimal_minutos }} minutos
                @else
                    Menos de um minuto
                @endif
            @endif
        </nav>
        <br>
    @endif
