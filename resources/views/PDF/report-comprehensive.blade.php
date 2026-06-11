<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"/>
<title>التقرير الإحصائي الشامل</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @include('PDF._fonts')
  body { font-family:"Amiri","Cairo",serif; margin:0; padding:0; background:#fff; }
  .page { width:210mm; min-height:297mm; position:relative; page-break-after:always; overflow:hidden; }
  .page:last-child { page-break-after:auto; }
  @media print { body{-webkit-print-color-adjust:exact;print-color-adjust:exact;} }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════
     PAGE 1 — COVER + STUDENTS
     ═══════════════════════════════════════════ --}}
<div class="page flex flex-col">

  {{-- Header --}}
  <div style="background:linear-gradient(135deg,#042C53,#0d4a8a)" class="text-white px-8 pt-6 pb-4">
    <div class="flex items-center gap-5 mb-3">
      <img src="{{ public_path('images/yemen.logo.png') }}" class="w-20 h-20 object-contain" alt="شعار"/>
      <div class="flex-1">
        <p class="text-xs opacity-70">الجمهورية اليمنية</p>
        <h1 class="text-xl font-black">وزارة التربية والتعليم</h1>
        <p class="text-sm opacity-80 mt-0.5">مكتب التربية والتعليم — محافظة حضرموت الساحل — إدارة الامتحانات</p>
      </div>
      <div class="text-left text-xs leading-7 bg-white/10 rounded-xl px-4 py-2">
        <div>رقم التقرير: <b class="font-mono">RPT-{{ now()->format('Ymd') }}</b></div>
        <div>تاريخ الإصدار: <b>{{ now()->format('Y/m/d') }}</b></div>
        <div>العام الدراسي: <b>{{ $academicYear?->year ?? '—' }}</b></div>
      </div>
    </div>
    <div style="height:4px;background:linear-gradient(90deg,#b8860b,#f4c430,#b8860b)" class="rounded-full mb-3"></div>
    <div class="text-center pb-1">
      <h2 class="text-lg font-black">📊 التقرير الإحصائي الشامل</h2>
      <p class="text-xs opacity-70">إحصائيات الطلاب · المدارس · التنقلات · النتائج النهائية</p>
    </div>
  </div>

  {{-- Section 1: Students --}}
  <div class="px-8 py-5 flex-1">
    <div style="background:linear-gradient(135deg,#042C53,#1565c0)" class="text-white rounded-2xl px-5 py-3 flex items-center gap-3 mb-4">
      <span class="text-xl">👨‍🎓</span>
      <div class="flex-1">
        <h3 class="text-sm font-black">القسم الأول — إحصائيات الطلاب</h3>
        <p class="text-xs opacity-70">بيانات إجمالية لسجلات الطلاب في العام الدراسي النشط</p>
      </div>
      <div class="bg-white/20 rounded-xl px-3 py-1.5 text-center">
        <div class="text-xl font-black">{{ number_format($studentsStats['total']) }}</div>
        <div class="text-xs opacity-75">إجمالي</div>
      </div>
    </div>

    <div class="grid grid-cols-4 gap-3 mb-4">
      <div style="background:#1e3a5f" class="text-white rounded-xl p-4">
        <div class="text-xs opacity-70 mb-1">إجمالي الطلاب</div>
        <div class="text-3xl font-black">{{ number_format($studentsStats['total']) }}</div>
        <div class="text-xs opacity-60 mt-1">مسجلون هذا العام</div>
      </div>
      <div style="background:#14532d" class="text-white rounded-xl p-4">
        <div class="text-xs opacity-70 mb-1">طلاب جدد</div>
        <div class="text-3xl font-black">{{ number_format($studentsStats['new']) }}</div>
        <div class="text-xs opacity-60 mt-1">تسجيل جديد</div>
      </div>
      <div style="background:#78350f" class="text-white rounded-xl p-4">
        <div class="text-xs opacity-70 mb-1">منقولون</div>
        <div class="text-3xl font-black">{{ number_format($studentsStats['villages']) }}</div>
        <div class="text-xs opacity-60 mt-1">بين المدارس</div>
      </div>
      <div style="background:#7f1d1d" class="text-white rounded-xl p-4">
        <div class="text-xs opacity-70 mb-1">معيدون / راسبون</div>
        <div class="text-3xl font-black">{{ number_format($studentsStats['repeaters']) }}</div>
        <div class="text-xs opacity-60 mt-1">يحتاجون متابعة</div>
      </div>
    </div>

    {{-- YoY Bar --}}
    @php $pct = $studentsStats['previousYearPercentage']; @endphp
    <div class="bg-gray-50 border border-gray-200 rounded-xl px-5 py-3 flex items-center gap-5">
      <div class="text-sm font-bold text-gray-600">مقارنة بالعام السابق:</div>
      <div class="text-2xl font-black {{ $pct >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
        {{ $pct > 0 ? '+' : '' }}{{ $pct }}%
      </div>
      <div class="text-sm {{ $pct >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
        {{ $pct >= 0 ? '▲ نمو في عدد الطلاب' : '▼ تراجع في عدد الطلاب' }}
      </div>
    </div>
  </div>

  {{-- Page Footer --}}
  <div class="bg-gray-50 border-t border-gray-200 px-8 py-2 flex justify-between items-center text-xs text-gray-400">
    <span>نظام إدارة بيانات الطلاب — مكتب الامتحانات</span>
    <span>صفحة 1 من 3</span>
  </div>
</div>


{{-- ═══════════════════════════════════════════
     PAGE 2 — SCHOOLS + TRANSFERS
     ═══════════════════════════════════════════ --}}
<div class="page flex flex-col">

  {{-- Mini Header --}}
  <div style="background:linear-gradient(135deg,#042C53,#0d4a8a)" class="text-white px-8 py-3 flex items-center justify-between">
    <div>
      <p class="text-xs opacity-70">التقرير الإحصائي الشامل</p>
      <h2 class="text-sm font-black">المدارس والتنقلات — العام {{ $academicYear?->year ?? '—' }}</h2>
    </div>
    <img src="{{ public_path('images/yemen.logo.png') }}" class="w-10 h-10 object-contain opacity-80" alt="شعار"/>
  </div>
  <div style="height:3px;background:linear-gradient(90deg,#b8860b,#f4c430,#b8860b)"></div>

  <div class="px-8 py-5 flex-1 space-y-6">

    {{-- Section 2: Schools --}}
    <div>
      <div style="background:linear-gradient(135deg,#14532d,#16a34a)" class="text-white rounded-2xl px-5 py-3 flex items-center gap-3 mb-4">
        <span class="text-xl">🏫</span>
        <div class="flex-1">
          <h3 class="text-sm font-black">القسم الثاني — إحصائيات المدارس</h3>
          <p class="text-xs opacity-70">نظرة شاملة على المدارس وطاقتها الاستيعابية</p>
        </div>
        <div class="bg-white/20 rounded-xl px-3 py-1.5 text-center">
          <div class="text-xl font-black">{{ number_format($schoolsStats['total']) }}</div>
          <div class="text-xs opacity-75">مدرسة</div>
        </div>
      </div>

      <div class="grid grid-cols-4 gap-3 mb-3">
        <div style="background:#14532d" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">إجمالي المدارس</div>
          <div class="text-3xl font-black">{{ number_format($schoolsStats['total']) }}</div>
          <div class="text-xs opacity-60 mt-1">في نطاق المديرية</div>
        </div>
        <div style="background:#0f4c75" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">إجمالي الطلاب</div>
          <div class="text-3xl font-black">{{ number_format($schoolsStats['totalStudents']) }}</div>
          <div class="text-xs opacity-60 mt-1">موزعون على المدارس</div>
        </div>
        <div style="background:#7c2d12" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">مدارس حكومية</div>
          <div class="text-3xl font-black">{{ number_format($schoolsStats['publicCount'] ?? ($schoolsStats['total'] - $schoolsStats['suspended'])) }}</div>
          <div class="text-xs opacity-60 mt-1">من إجمالي المدارس</div>
        </div>
        <div style="background:#581c87" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">مدارس أهلية</div>
          <div class="text-3xl font-black">{{ number_format($schoolsStats['privateCount'] ?? $schoolsStats['suspended']) }}</div>
          <div class="text-xs opacity-60 mt-1">من إجمالي المدارس</div>
        </div>
      </div>

      {{-- Extra metrics --}}
      <div class="bg-gray-50 border border-gray-200 rounded-xl px-5 py-3 flex items-center gap-8">
        @php
          $total = $schoolsStats['total'];
          $avgStudents = $total > 0 ? number_format($schoolsStats['totalStudents'] / $total, 1) : '—';
          $overcrowded = $schoolsStats['overcrowded'];
          $publicPct = $total > 0 ? number_format(($schoolsStats['publicCount'] ?? ($total - $schoolsStats['suspended'])) / $total * 100, 1) : 0;
        @endphp
        <div class="text-center">
          <div class="text-2xl font-black text-emerald-700">{{ $avgStudents }}</div>
          <div class="text-xs text-gray-500">متوسط الطلاب / مدرسة</div>
        </div>
        <div class="w-px h-10 bg-gray-300"></div>
        <div class="text-center">
          <div class="text-2xl font-black text-orange-600">{{ $overcrowded }}</div>
          <div class="text-xs text-gray-500">مدرسة مكتظة (فوق 90%)</div>
        </div>
        <div class="w-px h-10 bg-gray-300"></div>
        <div class="text-center">
          <div class="text-2xl font-black text-blue-700">{{ $publicPct }}%</div>
          <div class="text-xs text-gray-500">نسبة المدارس الحكومية</div>
        </div>
      </div>
    </div>

    {{-- Divider --}}
    <div class="border-t border-dashed border-gray-300"></div>

    {{-- Section 3: Transfers --}}
    <div>
      <div style="background:linear-gradient(135deg,#78350f,#d97706)" class="text-white rounded-2xl px-5 py-3 flex items-center gap-3 mb-4">
        <span class="text-xl">🔄</span>
        <div class="flex-1">
          <h3 class="text-sm font-black">القسم الثالث — إحصائيات التنقلات</h3>
          <p class="text-xs opacity-70">حركة نقل الطلاب بين المدارس خلال العام الدراسي</p>
        </div>
        <div class="bg-white/20 rounded-xl px-3 py-1.5 text-center">
          <div class="text-xl font-black">{{ number_format($transfersStats['total']) }}</div>
          <div class="text-xs opacity-75">طلب نقل</div>
        </div>
      </div>

      <div class="grid grid-cols-4 gap-3 mb-3">
        <div style="background:#78350f" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">إجمالي الطلبات</div>
          <div class="text-3xl font-black">{{ number_format($transfersStats['total']) }}</div>
          <div class="text-xs opacity-60 mt-1">خلال العام الحالي</div>
        </div>
        <div style="background:#713f12" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">قيد الانتظار</div>
          <div class="text-3xl font-black">{{ number_format($transfersStats['pending']) }}</div>
          <div class="text-xs opacity-60 mt-1">تنتظر الموافقة</div>
        </div>
        <div style="background:#14532d" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">موافق عليها</div>
          <div class="text-3xl font-black">{{ number_format($transfersStats['approved']) }}</div>
          <div class="text-xs opacity-60 mt-1">تم النقل بنجاح</div>
        </div>
        <div style="background:#7f1d1d" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">مرفوضة</div>
          <div class="text-3xl font-black">{{ number_format($transfersStats['rejected']) }}</div>
          <div class="text-xs opacity-60 mt-1">تم رفضها</div>
        </div>
      </div>

      @if($transfersStats['total'] > 0)
      <div class="bg-gray-50 border border-gray-200 rounded-xl px-5 py-3 flex items-center gap-8">
        <div class="text-center">
          <div class="text-2xl font-black text-emerald-600">{{ number_format($transfersStats['approved']/$transfersStats['total']*100,1) }}%</div>
          <div class="text-xs text-gray-500">نسبة الموافقة</div>
        </div>
        <div class="w-px h-10 bg-gray-300"></div>
        <div class="text-center">
          <div class="text-2xl font-black text-red-600">{{ number_format($transfersStats['rejected']/$transfersStats['total']*100,1) }}%</div>
          <div class="text-xs text-gray-500">نسبة الرفض</div>
        </div>
        <div class="w-px h-10 bg-gray-300"></div>
        <div class="text-center">
          <div class="text-2xl font-black text-amber-600">{{ number_format($transfersStats['pending']/$transfersStats['total']*100,1) }}%</div>
          <div class="text-xs text-gray-500">قيد المعالجة</div>
        </div>
      </div>
      @endif
    </div>

  </div>

  {{-- Page Footer --}}
  <div class="bg-gray-50 border-t border-gray-200 px-8 py-2 flex justify-between items-center text-xs text-gray-400">
    <span>نظام إدارة بيانات الطلاب — مكتب الامتحانات</span>
    <span>صفحة 2 من 3</span>
  </div>
</div>


{{-- ═══════════════════════════════════════════
     PAGE 3 — RESULTS + SIGNATURES
     ═══════════════════════════════════════════ --}}
<div class="page flex flex-col">

  {{-- Mini Header --}}
  <div style="background:linear-gradient(135deg,#042C53,#0d4a8a)" class="text-white px-8 py-3 flex items-center justify-between">
    <div>
      <p class="text-xs opacity-70">التقرير الإحصائي الشامل</p>
      <h2 class="text-sm font-black">النتائج النهائية — العام {{ $academicYear?->year ?? '—' }}</h2>
    </div>
    <img src="{{ public_path('images/yemen.logo.png') }}" class="w-10 h-10 object-contain opacity-80" alt="شعار"/>
  </div>
  <div style="height:3px;background:linear-gradient(90deg,#b8860b,#f4c430,#b8860b)"></div>

  <div class="px-8 py-5 flex-1 space-y-5">

    {{-- Section 4: Results --}}
    <div>
      <div style="background:linear-gradient(135deg,#312e81,#4f46e5)" class="text-white rounded-2xl px-5 py-3 flex items-center gap-3 mb-4">
        <span class="text-xl">🎓</span>
        <div class="flex-1">
          <h3 class="text-sm font-black">القسم الرابع — إحصائيات النتائج النهائية</h3>
          <p class="text-xs opacity-70">ملخص نتائج الطلاب وتحليل مستوى الأداء الدراسي</p>
        </div>
        <div class="bg-white/20 rounded-xl px-3 py-1.5 text-center">
          <div class="text-xl font-black">{{ $resultsStats['passRate'] }}%</div>
          <div class="text-xs opacity-75">نسبة النجاح</div>
        </div>
      </div>

      <div class="grid grid-cols-4 gap-3 mb-4">
        <div style="background:#312e81" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">نسبة النجاح</div>
          <div class="text-3xl font-black">{{ $resultsStats['passRate'] }}%</div>
          <div class="text-xs opacity-60 mt-1">العام الماضي: {{ $resultsStats['previousYearPassRate'] }}%</div>
        </div>
        <div style="background:#7f1d1d" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">نسبة الرسوب</div>
          <div class="text-3xl font-black">{{ $resultsStats['failRate'] }}%</div>
          <div class="text-xs opacity-60 mt-1">من إجمالي الممتحنين</div>
        </div>
        <div style="background:#1e3a5f" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">متوسط الدرجات</div>
          <div class="text-3xl font-black">{{ $resultsStats['average'] }}%</div>
          <div class="text-xs opacity-60 mt-1">لجميع المواد</div>
        </div>
        <div style="background:#78350f" class="text-white rounded-xl p-4">
          <div class="text-xs opacity-70 mb-1">أوائل المحافظة</div>
          <div class="text-3xl font-black">{{ number_format($resultsStats['topCount']) }}</div>
          <div class="text-xs opacity-60 mt-1">أعلى 3 من كل مدرسة</div>
        </div>
      </div>

      {{-- Pass Rate Progress --}}
      <div class="bg-gray-50 border border-gray-200 rounded-xl px-5 py-4 mb-4">
        <div class="flex justify-between text-xs font-bold mb-2">
          <span class="text-emerald-600">ناجح — {{ $resultsStats['passRate'] }}%</span>
          <span class="text-red-600">راسب — {{ $resultsStats['failRate'] }}%</span>
        </div>
        <div class="w-full h-5 bg-red-100 rounded-full overflow-hidden">
          <div class="h-full bg-emerald-500 rounded-full" style="width:{{ $resultsStats['passRate'] }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1.5">
          <span>بيانات العام {{ $academicYear?->year ?? '—' }}</span>
          <span>نسبة النجاح العام الماضي: {{ $resultsStats['previousYearPassRate'] }}%</span>
        </div>
      </div>

      {{-- Top Student --}}
      @if($resultsStats['highestStudent'])
      <div style="background:linear-gradient(135deg,#fef3c7,#fde68a)" class="border border-amber-200 rounded-xl px-5 py-4 flex items-center gap-4">
        <div class="w-14 h-14 bg-amber-400 rounded-full flex items-center justify-center text-2xl flex-shrink-0">🏆</div>
        <div class="flex-1">
          <div class="text-xs text-amber-700 font-bold">الأول على المحافظة — أعلى معدل تحصيلي</div>
          <div class="text-lg font-black text-gray-800 mt-0.5">{{ $resultsStats['highestStudent']['name'] }}</div>
        </div>
        <div class="text-center">
          <div class="text-4xl font-black text-amber-600">{{ $resultsStats['highestStudent']['average'] }}%</div>
          <div class="text-xs text-amber-700">المعدل العام</div>
        </div>
      </div>
      @endif
    </div>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Signatures --}}
    <div class="border border-gray-200 rounded-xl p-5 bg-gray-50">
      <p class="text-xs font-bold text-gray-500 text-center mb-5 uppercase tracking-wider">ملاحظات وتوقيعات المسؤولين</p>
      <div class="grid grid-cols-3 gap-8">
        <div class="text-center">
          <div class="h-12 border-b border-dashed border-gray-400 mb-2"></div>
          <p class="text-xs text-gray-600 font-bold">رئيس قسم الامتحانات</p>
        </div>
        <div class="text-center">
          <div class="h-12 border-b border-dashed border-gray-400 mb-2"></div>
          <p class="text-xs text-gray-600 font-bold">مدير إدارة التربية</p>
        </div>
        <div class="text-center">
          <div class="h-12 border-b border-dashed border-gray-400 mb-2"></div>
          <p class="text-xs text-gray-600 font-bold">مدير مكتب التربية والتعليم</p>
        </div>
      </div>
    </div>

  </div>

  {{-- Final Footer --}}
  <div style="background:linear-gradient(135deg,#042C53,#0d4a8a)" class="text-white px-8 py-4 flex justify-between items-center">
    <div class="text-xs opacity-75">
      <div class="font-bold text-sm opacity-90 mb-0.5">نظام إدارة بيانات الطلاب — مكتب الامتحانات</div>
      <div>هذا التقرير صادر آلياً — {{ now()->format('H:i — Y/m/d') }}</div>
    </div>
    <div class="text-xs opacity-75 text-left">صفحة 3 من 3</div>
  </div>

</div>{{-- end page 3 --}}
</body>
</html>
