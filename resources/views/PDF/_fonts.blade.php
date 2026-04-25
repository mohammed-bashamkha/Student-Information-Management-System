{!! \Illuminate\Support\Facades\Cache::remember('pdf_fonts_css', 86400, fn() => file_get_contents(public_path('fonts/pdf-fonts.css'))) !!}
