<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>طلب استخراج بدلاً عن فاقد - نسخة الطالب</title>
    <style>
        @include('PDF._fonts')

      @page {
        size: A4;
        margin: 0;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: "Amiri", "Cairo", "Traditional Arabic", serif;
        background: #e8e8e8;
        display: flex;
        justify-content: center;
        padding: 0;
        margin: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .page {
        width: 210mm;
        height: 297mm;
        background: white;
        padding: 12mm 18mm 10mm 18mm;
        position: relative;
        overflow: hidden;
      }

      /* Decorative top accent */
      .page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: #d1d5db;
      }

      /* Header Section */
      .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 2px solid #d1d5db;
        direction: rtl;
      }

      .header-right {
        text-align: right;
        font-size: 14px;
        font-weight: bold;
        line-height: 2;
        color: #000;
      }

      .header-center {
        text-align: center;
        padding: 0 10px;
      }

      .header-center img {
        width: 120px;
        height: auto;
      }

      .header-left {
        text-align: left;
        font-size: 13px;
        font-weight: bold;
        line-height: 2.2;
        color: #000;
      }

      .header-left span {
        letter-spacing: 1px;
        color: #000;
      }

      /* Main Title */
      .main-title {
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        margin: 12px 0 10px;
        color: #000;
        letter-spacing: 1px;
        position: relative;
        padding-bottom: 8px;
      }

      .main-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #d1d5db, transparent);
      }

      /* Sections */
      .section {
        border: 1.5px solid #d1d5db;
        margin: 10px 0;
        border-radius: 4px;
        overflow: hidden;
      }

      .section-title {
        background: #f3f4f6;
        color: #000;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        padding: 6px 10px;
        letter-spacing: 0.5px;
      }

      .section-content {
        padding: 10px 12px;
      }

      /* Student Photo & Form Container */
      .first-section-content {
        display: flex;
        direction: rtl;
      }

      .form-fields {
        flex: 1;
        padding: 10px 12px;
      }

      .photo-area {
        width: 140px;
        height: 170px;
        border: 1.5px solid #d1d5db;
        margin: 10px;
        overflow: hidden;
        flex-shrink: 0;
        background: #f8f9fa;
      }

      .photo-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
      }

      .photo-area .photo-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f2f5;
        color: #000;
        font-size: 13px;
        text-align: center;
      }

      /* Form Row */
      .form-row {
        display: flex;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin-bottom: 8px;
        font-size: 13px;
        direction: rtl;
      }

      .form-row.full-width {
        flex-wrap: nowrap;
      }

      .form-field {
        display: flex;
        align-items: baseline;
        margin-left: 15px;
        flex: 1;
        min-width: 45%;
      }

      .form-field.full {
        min-width: 100%;
      }

      .form-field label {
        font-weight: bold;
        white-space: nowrap;
        margin-left: 5px;
        font-size: 13px;
        color: #000;
      }

      .form-field .dots {
        flex: 1;
        border-bottom: 1px dotted #999;
        margin-right: 5px;
        min-width: 50px;
        height: 18px;
        font-size: 13px;
        color: #000;
        padding-bottom: 1px;
      }

      /* Disclaimer text */
      .disclaimer {
        text-align: center;
        font-size: 12px;
        margin: 8px 0;
        padding: 5px 10px;
        color: #000;
        font-style: italic;
      }

      /* Signature row */
      .signature-row {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin: 6px 0;
        direction: rtl;
      }

      .signature-field {
        display: flex;
        align-items: baseline;
      }

      .signature-field label {
        font-weight: bold;
        margin-left: 5px;
        white-space: nowrap;
        color: #000;
        font-size: 12px;
      }

      .signature-field .dots {
        border-bottom: 1px dotted #999;
        min-width: 80px;
        height: 18px;
        font-size: 12px;
      }

      /* Checkboxes */
      .checkbox-row {
        display: flex;
        gap: 15px;
        margin-top: 8px;
        font-size: 12px;
        flex-wrap: wrap;
      }

      .checkbox-item {
        display: flex;
        align-items: center;
        gap: 4px;
      }

      .checkbox-item input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: #000;
      }

      .checkbox-item label {
        font-size: 12px;
      }

      /* Grades Table */
      .grades-section {
        margin: 10px 0;
      }

      .grades-title {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        padding: 6px;
        color: #000;
        position: relative;
        margin-bottom: 6px;
      }

      .grades-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #d1d5db, transparent);
      }

      .grades-table {
        width: 100%;
        border-collapse: collapse;
      }

      .grades-table th,
      .grades-table td {
        border: 1px solid #d1d5db;
        padding: 5px 6px;
        text-align: center;
        font-size: 11px;
      }

      .grades-table th {
        background: #f3f4f6;
        color: #000;
        font-weight: bold;
        font-size: 11px;
      }

      .grades-table td {
        height: 25px;
        background: white;
      }

      .grades-table tbody tr:nth-child(even) td {
        background: #f4f7fa;
      }

      .grades-table .subject-col {
        width: 110px;
        font-weight: bold;
        color: #000;
        background: #f3f4f6 !important;
      }

      .grades-table .total-col,
      .grades-table .average-col {
        width: 60px;
        font-weight: bold;
      }

      /* Footer signatures */
      .footer-signatures {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1.5px solid #d1d5db;
        font-size: 12px;
        text-align: center;
        direction: rtl;
      }

      .footer-sig {
        text-align: center;
        min-width: 140px;
      }

      .footer-sig .name {
        font-weight: bold;
        margin-bottom: 4px;
        color: #000;
        font-size: 12px;
      }

      .footer-sig .title {
        font-size: 10px;
        font-weight: bold;
        color: #000;
      }

      /* Requirements footer */
      .requirements {
        margin-top: 10px;
        font-size: 9px;
        line-height: 1.8;
        border-top: 1px solid #d1d5db;
        padding-top: 6px;
        text-align: right;
        color: #000;
      }

      /* Decorative bottom accent */
      .page::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #d1d5db;
      }

      @media print {
        body {
          background: white;
          padding: 0;
        }
        .page {
          width: 210mm;
          height: 297mm;
          box-shadow: none;
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
          <div>إدارة التربية و التعليم م / المكلا</div>
          <div>محافظة حضرموت الساحل</div>
        </div>
        <div class="header-center">
          <img src="{{ public_path('images/yemen.logo.png') }}" alt="شعار الجمهورية اليمنية">
        </div>
        <div class="header-left">
          <div>رقم النموذج: <span>{{ $certificate->id }}</span></div>
          <div>التاريخ: <span>{{ $printDate }}</span></div>
        </div>
      </div>

      <!-- Main Title -->
      <div class="main-title">طلب استخراج بدلاً عن فاقد</div>

      <!-- Section 1: خاص بالمتقدم -->
      <div class="section">
        <div class="section-title">أولاً: خاص بالمتقدم</div>
        <div class="first-section-content">
          <div class="form-fields">
            <div class="form-row">
              <div class="form-field full">
                <label>أسم الطالب الرباعي:</label>
                <div class="dots">{{ $student->full_name ?? '' }}</div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>محل الميلاد:</label>
                <div class="dots">{{ $student->birth_place ?? '' }}</div>
              </div>
              <div class="form-field">
                <label>تاريخ الميلاد:</label>
                <div class="dots">{{ $student->birth_date ?? '' }}</div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>المدرسة:</label>
                <div class="dots">{{ $school->name ?? '' }}</div>
              </div>
              <div class="form-field">
                <label>المديرية:</label>
                <div class="dots">{{ $school->district ?? '' }}</div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>نوع الشهادة:</label>
                <div class="dots">{{ $certificate->certificate_type ?? '' }}</div>
              </div>
              <div class="form-field">
                <label>العام الدراسي:</label>
                <div class="dots">{{ $academicYear->year ?? '' }}</div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-field">
                <label>القسم:</label>
                <div class="dots">{{ $schoolClass->name ?? '' }}</div>
              </div>
              <div class="form-field">
                <label>رقم الجلوس:</label>
                <div class="dots">{{ $student->seat_number ?? '' }}</div>
              </div>
            </div>
          </div>
          <!-- Student Photo -->
          <div class="photo-area">
            @if($certificate->student_image)
              <img src="{{ public_path('storage/' . $certificate->student_image) }}" alt="">
            @else
              <div class="photo-placeholder">صورة<br />الطالب</div>
            @endif
          </div>
        </div>
      </div>

      <!-- Disclaimer -->
      <div class="disclaimer">
        وأتحمل مسئولية أي أخطاء وردت في طلبي هذا ، والله الموفق .
      </div>

      <!-- Signature Row -->
      <div class="signature-row">
        <div class="signature-field">
          <label>أسم المتقدم:</label>
          <div class="dots"></div>
        </div>
        <div class="signature-field">
          <label>توقيعة:</label>
          <div class="dots"></div>
        </div>
        <div class="signature-field">
          <label>تاريخ الطلب:</label>
          <div class="dots">{{ $printDate }}</div>
        </div>
      </div>

      <!-- Section 2: خاص بإدارة الاختبارات -->
      <div class="section">
        <div class="section-title">ثانياً: خاص بإدارة الاختبارات</div>
        <div class="section-content">
          <div class="form-row">
            <div class="form-field full">
              <label>أسم الطالب الرباعي:</label>
              <div class="dots">{{ $student->full_name ?? '' }}</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label>محل الميلاد:</label>
              <div class="dots">{{ $student->birth_place ?? '' }}</div>
            </div>
            <div class="form-field">
              <label>تاريخ الميلاد:</label>
              <div class="dots">{{ $student->birth_date ?? '' }}</div>
            </div>
            <div class="form-field">
              <label>المدرسة:</label>
              <div class="dots">{{ $school->name ?? '' }}</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label>المديرية:</label>
              <div class="dots">{{ $school->district ?? '' }}</div>
            </div>
            <div class="form-field">
              <label>نوع الشهادة:</label>
              <div class="dots">{{ $certificate->certificate_type ?? '' }}</div>
            </div>
            <div class="form-field">
              <label>العام الدراسي:</label>
              <div class="dots">{{ $academicYear->year ?? '' }}</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label>القسم:</label>
              <div class="dots">{{ $schoolClass->name ?? '' }}</div>
            </div>
            <div class="form-field">
              <label>رقم الجلوس:</label>
              <div class="dots">{{ $student->seat_number ?? '' }}</div>
            </div>
          </div>
          <div class="checkbox-row">
            <div class="checkbox-item">
              <label>بطاقة شخصية</label>
              <input type="checkbox" />
            </div>
            <div class="checkbox-item">
              <label>جواز سفر</label>
              <input type="checkbox" />
            </div>
            <div class="checkbox-item">
              <label>شهادة ميلاد</label>
              <input type="checkbox" />
            </div>
            <div class="checkbox-item">
              <label>أخرى</label>
              <input type="checkbox" />
            </div>
          </div>
        </div>
      </div>

      <!-- Grades Section -->
      <div class="grades-section">
        <div class="grades-title">بيان الدرجات</div>
        <table class="grades-table">
          <thead>
            <tr>
                <th class="subject-col">المـــادة</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="average-col">المعـــدل</th>
                <th class="total-col">المجمـــوع</th>
            </tr>
          </thead>
          <tbody>
            <tr>
                <td class="subject-col">النهاية الكبرى</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
                <td class="subject-col">النهاية الصغرى</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
                <td class="subject-col">الدرجة المتحصل عليها</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Signatures -->
      <div class="footer-signatures">
        <div class="footer-sig">
          <div class="name">{{ $createdBy->name ?? '' }}</div>
          <div class="title">المختص</div>
        </div>
        <div class="footer-sig">
          <div class="name"></div>
          <div class="title">رئيس قسم الاختبارات</div>
        </div>
        <div class="footer-sig">
          <div class="name"></div>
          <div class="title">
            مدير عام مكتب وزارة التربية و التعليم م / حضرموت
          </div>
        </div>
      </div>

      <!-- Requirements -->
      <div class="requirements">
        المطلوب من المتقدم: 1- ملف, 2- إخطاء طرف, 3- رسالة بدل فاقد من إدارة
        التربية و التعليم, 4- أربع صور شمسية مدرسية بخلفية بيضاء,
        <br />
        5- صورة معمدة طبق الاصل من البطاقة الشخصية أو جواز السفر أو شهادة
        الميلاد, 6- تعبئة الاستمارة
      </div>
    </div>
  </body>
</html>
