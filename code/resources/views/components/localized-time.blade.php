@props(['datetime' => null, 'formatOptions' => null, 'id' => null])
@php
use Illuminate\Support\Str;
use Carbon\Carbon;

// compute iso and id
$iso = null;
if ($datetime) {
    try {
        if (method_exists($datetime, 'toIsoString')) {
            $iso = $datetime->toIsoString();
        } else {
            $iso = Carbon::parse($datetime)->toIsoString();
        }
    } catch (\Exception $e) {
        $iso = null;
    }
}

$id = $id ?? 'localized-time-'.Str::random(8);

$formatOptions = $formatOptions ?? ['day' => '2-digit', 'month' => 'short', 'year' => 'numeric', 'hour' => '2-digit', 'minute' => '2-digit'];
$formatJson = json_encode($formatOptions);
@endphp

@if(!$iso)
    —
@else
    <time id="{{ $id }}" datetime="{{ $iso }}">{{ $iso }}</time>
    <script>(function(){
        try{
            var el = document.getElementById({{ Illuminate\Support\Js::from($id) }});
            if(!el) return;
            var iso = {{ Illuminate\Support\Js::from($iso) }};
            var dt = new Date(iso);
            if (isNaN(dt.getTime())) return;

            function pad(n){ return n < 10 ? '0' + n : String(n); }
            var day = pad(dt.getDate());
            var year = dt.getFullYear();
            var hour = pad(dt.getHours());
            var minute = pad(dt.getMinutes());

            // Localized short month name using Intl (keeps locale-specific abbreviations)
            var locale = (navigator && navigator.language) ? navigator.language : undefined;
            var monthShort = null;
            try {
                monthShort = new Intl.DateTimeFormat(locale, { month: 'short' }).format(dt);
            } catch (e) {
                // Fallback to English short names
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                monthShort = months[dt.getMonth()];
                console.log('Error formatting month with Intl:', e);
            }

            // PHP format: d M Y, H:i => 02 Jan 2020, 14:05 (with localized month)
            var formatted = day + ' ' + monthShort + ' ' + year + ', ' + hour + ':' + minute;
            el.textContent = formatted;
        }catch(e){
            console && console.error && console.error(e);
        }
    })();</script>
@endif
