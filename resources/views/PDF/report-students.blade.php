<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>تقرير الطلاب الشامل</title>
  <style>
    @include('PDF._fonts')
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:"Amiri","Cairo","Traditional Arabic",serif; background:#fff; direction:rtl; font-size:11px; color:#1a1a2e; }

    /* ── PAGE ── */
    .page { width:210mm; min-height:297mm; padding:0; position:relative; }

    /* ── HEADER ── */
    .header { background:linear-gradient(135deg,#042C53 0%,#0d4a8a 100%); color:#fff; padding:14px 16px 10px; display:flex; align-items:center; gap:14px; }
    .header-logo { width:68px; height:68px; object-fit:contain; flex-shrink:0; }
    .header-text { flex:1; }
    .header-ministry { font-size:13px; font-weight:bold; opacity:.85; margin-bottom:2px; }
    .header-title { font-size:18px; font-weight:bold; line-height:1.3; }
    .header-sub { font-size:11px; opacity:.75; margin-top:3px; }
    .header-meta { text-align:left; font-size:10px; opacity:.8; line-height:1.8; }

    /* ── ACCENT BAR ── */
    .accent-bar { height:5px; background:linear-gradient(90deg,#f4c430 0%,#e8a000 50%,#f4c430 100%); }

    /* ── REPORT TITLE BAND ── */
    .report-band { background:#f8f9fc; border-bottom:1.5px solid #e2e8f0; padding:10px 16px; display:flex; align-items:center; justify-content:space-between; }
    .report-band-title { font-size:15px; font-weight:bold; color:#042C53; }
    .report-band-date { font-size:10px; color:#64748b; }

    /* ── STATS ROW ── */
    .stats-row { display:flex; gap:0; border-bottom:1.5px solid #e2e8f0; }
    .stat-box { flex:1; padding:10px 12px; text-align:center; border-left:1px solid #e2e8f0; }
    .stat-box:last-child { border-left:none; }
    .stat-val { font-size:22px; font-weight:bold; color:#042C53; line-height:1; }
    .stat-val.green { color:#16a34a; }
    .stat-val.amber { color:#d97706; }
    .stat-val.red { color:#dc2626; }
    .stat-lbl { font-size:9.5px; color:#64748b; margin-top:3px; }

    /* ── TABLE ── */
    .section-title { font-size:12px; font-weight:bold; color:#042C53; padding:8px 16px 6px; border-bottom:1px solid #e2e8f0; background:#f1f5f9; display:flex; align-items:center; gap:6px; }
    .section-title::before { content:''; display:inline-block; width:4px; height:14px; background:#f4c430; border-radius:2px; }

    table.data-table { width:100%; border-collapse:collapse; font-size:10px; }
    table.data-table thead tr { background:#042C53; color:#fff; }
    table.data-table thead th { padding:7px 8px; font-weight:bold; font-size:10px; text-align:center; }
    table.data-table thead th:first-child { text-align:right; padding-right:14px; }
    table.data-table tbody tr:nth-child(even) { background:#f8fafc; }
    table.data-table tbody tr:nth-child(odd)  { background:#ffffff; }
    table.data-table tbody td { padding:6px 8px; vertical-align:middle; text-align:center; border-bottom:1px solid #e2e8f0; }
    table.data-table tbody td:first-child { text-align:right; padding-right:14px; }
    .badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:9px; font-weight:bold; }
    .badge-green { background:#dcfce7; color:#166534; }
    .badge-red   { background:#fee2e2; color:#991b1b; }
    .badge-amber { background:#fef3c7; color:#92400e; }
    .badge-blue  { background:#dbeafe; color:#1e40af; }
    .row-num { color:#94a3b8; font-size:9px; }
    .name-cell { font-weight:bold; color:#1e293b; }
    .sub-text { font-size:8.5px; color:#94a3b8; }

    /* ── FOOTER ── */
    .footer { background:#f8f9fc; border-top:1.5px solid #e2e8f0; padding:10px 16px; display:flex; align-items:center; justify-content:space-between; position:fixed; bottom:0; left:0; right:0; }
    .footer-left { font-size:9px; color:#64748b; }
    .footer-right { font-size:9px; color:#64748b; display:flex; align-items:center; gap:8px; }
    .footer-sig { border-top:1px solid #94a3b8; min-width:120px; text-align:center; padding-top:4px; font-size:9px; color:#475569; }
    .page-num { font-size:9px; color:#94a3b8; }

    @media print { body{background:#fff;} }
  </style>
</head>
<body>
<div class="page">

  <!-- HEADER -->
  <div class="header">
    <img class="header-logo" src="{{ public_path('images/yemen.logo.png') }}" alt="شعار الجمهورية" />
    <div class="header-text">
      <div class="header-ministry">الجمهورية اليمنية — وزارة التربية والتعليم</div>
      <div class="header-title">مكتب التربية والتعليم — محافظة حضرموت الساحل</div>
      <div class="header-sub">إدارة الامتحانات والقياس والتقويم</div>
    </div>
    <div class="header-meta">
      <div>رقم التقرير: <strong>STD-{{ now()->format('Ymd') }}</strong></div>
      <div>تاريخ الإصدار: <strong>{{ now()->format('Y/m/d') }}</strong></div>
      <div>العام الدراسي: <strong>{{ $academicYear->year ?? '—' }}</strong></div>
    </div>
  </div>
  <div class="accent-bar"></div>

  <!-- REPORT BAND -->
  <div class="report-band">
    <div class="report-band-title">📋 تقرير الطلاب الشامل</div>
    <div class="report-band-date">طُبع في: {{ now()->format('H:i — Y/m/d') }}</div>
  </div>

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat-box">
      <div class="stat-val">{{ number_format($stats['total']) }}</div>
      <div class="stat-lbl">إجمالي الطلاب</div>
    </div>
    <div class="stat-box">
      <div class="stat-val green">{{ number_format($stats['new']) }}</div>
      <div class="stat-lbl">طلاب جدد</div>
    </div>
    <div class="stat-box">
      <div class="stat-val amber">{{ number_format($stats['villages']) }}</div>
      <div class="stat-lbl">منقولون بين مدارس</div>
    </div>
    <div class="stat-box">
      <div class="stat-val red">{{ number_format($stats['repeaters']) }}</div>
      <div class="stat-lbl">معيدون / راسبون</div>
    </div>
    <div class="stat-box">
      <div class="stat-val {{ $stats['previousYearPercentage'] >= 0 ? 'green' : 'red' }}">
        {{ $stats['previousYearPercentage'] > 0 ? '+' : '' }}{{ $stats['previousYearPercentage'] }}%
      </div>
      <div class="stat-lbl">مقارنة بالعام الماضي</div>
    </div>
  </div>

  <!-- SECTION TITLE -->
  <div class="section-title">قائمة الطلاب المسجلين</div>

  <!-- DATA TABLE -->
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>اسم الطالب</th>
        <th>الرقم المدرسي</th>
        <th>الجنس</th>
        <th>المدرسة</th>
        <th>الصف</th>
        <th>الحالة</th>
        <th>تاريخ التسجيل</th>
      </tr>
    </thead>
    <tbody>
      @forelse($students as $index => $student)
      @php
        $enrollment = $student['enrollment'] ?? null;
        $statusMap = ['active' => ['ناجح / نشط','badge-green'], 'suspended' => ['موقوف','badge-red'], 'withdrawn' => ['منسحب','badge-amber']];
        $statusInfo = $statusMap[$enrollment['status'] ?? ''] ?? ['—', 'badge-blue'];
      @endphp
      <tr>
        <td class="row-num">{{ $index + 1 }}</td>
        <td style="text-align:right">
          <div class="name-cell">{{ $student['full_name'] ?? '—' }}</div>
        </td>
        <td style="direction:ltr; text-align:center">{{ $student['school_number'] ?? '—' }}</td>
        <td>{{ ($student['gender'] ?? '') === 'male' ? 'ذكر' : 'أنثى' }}</td>
        <td>{{ $enrollment['school']['name'] ?? ($student['school']['name'] ?? '—') }}</td>
        <td>{{ $enrollment['school_class']['name'] ?? ($student['school_class']['name'] ?? '—') }}</td>
        <td><span class="badge {{ $statusInfo[1] }}">{{ $statusInfo[0] }}</span></td>
        <td style="direction:ltr; font-size:9px;">{{ isset($student['created_at']) ? \Carbon\Carbon::parse($student['created_at'])->format('Y/m/d') : '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="8" style="text-align:center; padding:20px; color:#94a3b8;">لا توجد بيانات</td></tr>
      @endforelse
    </tbody>
  </table>

  <!-- FOOTER -->
  <div class="footer">
    <div class="footer-left">
      <div>نظام إدارة بيانات الطلاب — مكتب الامتحانات</div>
      <div class="page-num">إجمالي السجلات في هذا التقرير: {{ count($students) }}</div>
    </div>
    <div class="footer-sig">توقيع مدير مكتب الامتحانات</div>
  </div>

</div>
</body>
</html>
