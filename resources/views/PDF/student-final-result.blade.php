<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>شهادة تقييم أعمال السنة واختبار النقل</title>
    <style>
      {!! file_get_contents(public_path('fonts/pdf-fonts.css')) !!}

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: "Amiri", "Cairo", "Traditional Arabic", serif;
        background: #f5f5f5;
        display: flex;
        justify-content: center;
        padding: 20px;
      }

      .page {
        width: 210mm;
        min-height: 297mm;
        background: white;
        padding: 8mm 10mm;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        position: relative;
      }

      /* Header Section */
      .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        direction: rtl;
      }

      .header-right {
        text-align: right;
        font-size: 16px;
        font-weight: bold;
        line-height: 1.8;
      }

      .header-center {
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .header-center img {
        width: 150px;
        height: auto;
      }

      .header-left {
        text-align: left;
        font-size: 16px;
        font-weight: bold;
        line-height: 2;
      }

      .header-left span {
        letter-spacing: 2px;
      }

      /* Main Title */
      .main-title {
        text-align: center;
        font-size: 17px;
        font-weight: bold;
        margin: 3px 0;
        line-height: 1.5;
      }

      .main-title .subtitle {
        font-size: 15px;
        font-weight: bold;
      }

      /* Student Info */
      .student-info {
        padding: 6px 8px;
        font-size: 13px;
        font-weight: bold;
        direction: rtl;
        border: 1.5px solid #333;
        margin: 5px 0;
        background: #fdf6e3;
        line-height: 2;
      }

      .student-info-row {
        display: flex;
        width: 100%;
        gap: 10px;
        flex-wrap: wrap;
      }

      .info-item {
        display: flex;
        align-items: baseline;
        gap: 3px;
      }

      .info-item label {
        white-space: nowrap;
      }

      .info-item .value {
        border-bottom: 1px dotted #555;
        min-width: 80px;
        padding: 0 3px;
      }

      /* Main Table Container */
      .main-table-container {
        display: flex;
        direction: rtl;
        margin: 5px 0;
        gap: 0;
      }

      /* Grades Table */
      .grades-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
      }

      .grades-table th,
      .grades-table td {
        border: 1.5px solid #333;
        padding: 3px 4px;
        text-align: center;
        vertical-align: middle;
        height: 28px;
      }

      .grades-table th {
        background: #f0f0f0;
        font-weight: bold;
        font-size: 10.5px;
      }

      .grades-table td.subject-name {
        background: #f0f0f0;
        color: #333;
        font-weight: bold;
        font-size: 11px;
        white-space: nowrap;
        padding: 3px 6px;
        width: 85px;
        text-align: center;
      }

      .grades-table .total-label {
        background: #f0f0f0;
        font-weight: bold;
        font-size: 11px;
      }

      /* Signatures Column */
      .signatures-col {
        border: 1.5px solid #333;
        width: 140px;
        font-size: 10px;
        font-weight: bold;
        text-align: right;
        padding: 5px;
        line-height: 1.6;
        vertical-align: top;
        background: white;
      }

      .sig-item {
        margin-bottom: 5px;
      }

      .sig-item label {
        display: block;
      }

      .sig-item .sig-line {
        border-bottom: 1px dotted #666;
        min-height: 18px;
        margin-top: 2px;
      }

      /* Result Summary */
      .result-summary {
        display: flex;
        justify-content: space-between;
        padding: 5px 10px;
        font-size: 13px;
        font-weight: bold;
        direction: rtl;
        border: 1.5px solid #333;
        margin: 3px 0;
        background: #f0f0f0;
        align-items: center;
      }

      .summary-item {
        display: flex;
        align-items: baseline;
        gap: 5px;
      }

      .summary-item .value {
        min-width: 40px;
        text-align: center;
      }

      /* Final Result */
      .final-result {
        display: flex;
        justify-content: space-between;
        padding: 5px 10px;
        font-size: 13px;
        font-weight: bold;
        direction: rtl;
        border: 1.5px solid #333;
        margin: 3px 0;
        align-items: center;
      }

      .result-item {
        display: flex;
        align-items: baseline;
        gap: 5px;
      }

      .result-value {
        font-size: 14px;
        color: #006400;
        font-weight: bold;
      }

      /* Bottom Signatures */
      .bottom-signatures {
        margin-top: 10px;
        padding: 10px;
        border: 1.5px solid #333;
        background: #fdf6e3;
        direction: rtl;
        font-size: 12px;
        font-weight: bold;
      }
      
      .bottom-signatures .signatures-title {
        text-align: center;
        font-size: 14px;
        margin-bottom: 10px;
        text-decoration: underline;
      }

      .bottom-signatures .signatures-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        column-gap: 20px;
        row-gap: 15px;
      }

      /* Note */
      .note-text {
        font-size: 10px;
        text-align: right;
        margin-top: 4px;
        direction: rtl;
      }

      @media print {
        body {
          background: white;
          padding: 0;
        }
        .page {
          width: 100%;
          box-shadow: none;
          padding: 5mm;
          min-height: auto;
        }
      }
    </style>
  </head>
  <body>
    <div class="page">
      <!-- Header -->
      <div class="header">
        <div class="header-right">
          <div>الجمهورية اليمنية</div>
          <div>وزارة التربية والتعليم</div>
          <div>مكتب التربية والتعليم محافظة حضرموت الساحل</div>
        </div>
        <div class="header-center">
          <img src="{{ public_path('images/yemen.logo.png') }}" alt="شعار الجمهورية اليمنية" />
        </div>
        <div class="header-left">
          <div>مديرية : <span>{{ $school->district ?? 'المكلا' }}</span></div>
          <div>مدرسة : <span>{{ $school->name ?? '................' }}</span></div>
        </div>
      </div>

      <!-- Main Title -->
      <div class="main-title">
        كشف تقييم أعمال السنة واختبار النقل في المرحلة الثانوية
        <div class="subtitle">
          للصف {{ $schoolClass->name }} / للعام الدراسي {{ $academicYear->year }} م
        </div>
      </div>

      <!-- Student Info -->
      <div class="student-info">
        <div class="student-info-row">
          <div class="info-item">
            <label>اسم الطالب رباعي</label>
            <div class="value" style="min-width: 220px">{{ $student->full_name ?? '' }}</div>
          </div>
          <div class="info-item">
            <label>الصف</label>
            <div class="value" style="min-width: 100px">{{ $schoolClass->name ?? '' }}</div>
          </div>
          <div class="info-item">
            <label>الشعبة</label>
            <div class="value" style="min-width: 50px"></div>
          </div>
        </div>
        <div class="student-info-row">
          <div class="info-item">
            <label>الرقم المدرسي</label>
            <div class="value" style="min-width: 120px">{{ $student->school_number ?? '' }}</div>
          </div>
          <div class="info-item">
            <label>الجنسية</label>
            <div class="value" style="min-width: 50px">{{ $student->nationality ?? '' }}</div>
          </div>
          <div class="info-item">
            <label>الجنس</label>
            <div class="value" style="min-width: 40px">{{ $student->gender == 'male' ? 'ذكر' : 'انثى' }}</div>
          </div>
          <div class="info-item">
            <label>تاريخ الميلاد</label>
            <div class="value" style="min-width: 70px">{{ $student->date_of_birth ?? '' }}</div>
          </div>
          <div class="info-item">
            <label>محل الميلاد</label>
            <div class="value" style="min-width: 70px"></div>
          </div>
        </div>
      </div>

      <!-- Main Table with Signatures -->
      <table class="grades-table">
        <thead>
          <tr>
            <th rowspan="2" style="width: 85px;">المواد<br>الدراسية</th>
            <th>الفصل الدراسي الأول /50</th>
            <th>الفصل الدراسي الثاني /50</th>
            <th>المجموع الكلي /100</th>
            <th colspan="2">من المجموع /20%</th>
          </tr>
        </thead>
        <tbody>
          @foreach($subjects as $subject)
          @php
            $grade = $grades[$subject->id] ?? null;
          @endphp
          <tr>
            <td class="subject-name">{{ $subject->name }}</td>
            <td>{{ $grade->first_semester_total ?? '' }}</td>
            <td>{{ $grade->second_semester_total ?? '' }}</td>
            <td>{{ $grade->total ?? '' }}</td>
            <td>{{ $grade->percentage ?? '' }}</td>
            <td>{{ $grade->notes ?? '' }}</td>
          </tr>
          @endforeach

          <!-- المجموع row -->
          <tr>
            <td class="total-label">المجموع</td>
            <td>{{ $subjects->sum(fn($s) => $grades[$s->id]->first_semester_total ?? 0) }}</td>
            <td>{{ $subjects->sum(fn($s) => $grades[$s->id]->second_semester_total ?? 0) }}</td>
            <td>{{ $finalResult->total_student_grades ?? '' }}</td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>

      <!-- Total Result Summary -->
      <div class="result-summary">
        <div class="summary-item">
          <label>المجموع النهائي لدرجات الطالب</label>
        </div>
        <div class="summary-item">
          <label>رقماً</label>
          <div class="value">{{ $finalResult->total_student_grades ?? '............' }}</div>
        </div>
        <div class="summary-item">
          <label>كتابة</label>
          <div class="value" style="min-width: 180px">{{ $finalResult->total_score_text ?? '...................................' }}درجة</div>
        </div>
      </div>

      <!-- Final Result -->
      <div class="final-result">
        <div class="result-item">
          <label>النتيجة النهائية :</label>
          <div class="result-value">{{ $finalResult->final_result ?? '............' }}</div>
        </div>
        <div class="result-item">
          <label>النسبة</label>
          <div class="result-value">{{ $finalResult->average_grade . '%' ?? '............' }}</div>
        </div>
      </div>

      <!-- Bottom Signatures Section -->
      <div class="bottom-signatures">
        <div class="signatures-title">ملاحظات إدارة التربية والتعليم بالمديرية</div>
        <div class="signatures-grid">
          <div class="sig-item"><label>رئيس قسم الاختبارات</label><div class="sig-line"></div></div>
          <div class="sig-item"><label>رئيس قسم المتابعة</label><div class="sig-line"></div></div>
          <div class="sig-item"><label>مدير إدارة التربية والتعليم</label><div class="sig-line"></div></div>
          <div class="sig-item"><label>مدير إدارة التربية والتعليم في المنطقة</label><div class="sig-line"></div></div>
          <div class="sig-item"><label>رئيس قسم الاحتياج الأقسام</label><div class="sig-line"></div></div>
          <div class="sig-item"><label>مدير مكتب التربية والتعليم</label><div class="sig-line"></div></div>
        </div>
      </div>

      <!-- Note -->
      <div class="note-text">
        * أي خطأ أو خدش أو تغيير في البيانات الواردة في الكشف يلغيه
      </div>
    </div>
  </body>
</html>
