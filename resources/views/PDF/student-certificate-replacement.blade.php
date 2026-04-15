<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>طلب استخراج بدلاً عن فاقد - نسخة الطالب</title>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700&display=swap");

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
        padding: 10mm 20mm 10mm 20mm;
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
        font-size: 26px;
        font-weight: bold;
        margin: 20px 0 10px;
        text-decoration: underline;
        text-underline-offset: 8px;
      }

      /* Sections */
      .section {
        border: 2px solid #333;
        margin: 15px 0;
        position: relative;
      }

      .section-title {
        background: #e8e8e8;
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        padding: 8px;
        border-bottom: 2px solid #333;
      }

      .section-content {
        padding: 15px;
      }

      /* Student Photo & Form Container */
      .first-section-content {
        display: flex;
        direction: rtl;
      }

      .form-fields {
        flex: 1;
        padding: 15px;
      }

      .photo-area {
        width: 160px;
        min-height: 200px;
        border: 2px solid #333;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 15px;
        background: #fafafa;
      }

      .photo-area .photo-placeholder {
        width: 120px;
        height: 150px;
        border: 1px dashed #999;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        background: #f0f0f0;
        color: #888;
        font-size: 13px;
        text-align: center;
      }

      .photo-area .photo-label {
        font-size: 14px;
        font-weight: bold;
        color: #333;
      }

      /* Form Row */
      .form-row {
        display: flex;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin-bottom: 12px;
        font-size: 15px;
        direction: rtl;
      }

      .form-row.full-width {
        flex-wrap: nowrap;
      }

      .form-field {
        display: flex;
        align-items: baseline;
        margin-left: 20px;
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
        font-size: 15px;
      }

      .form-field .dots {
        flex: 1;
        border-bottom: 1px dotted #666;
        margin-right: 5px;
        min-width: 60px;
        height: 20px;
      }

      /* Disclaimer text */
      .disclaimer {
        text-align: center;
        font-size: 14px;
        margin: 15px 0;
        padding: 5px;
      }

      /* Signature row */
      .signature-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        margin: 10px 0;
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
      }

      .signature-field .dots {
        border-bottom: 1px dotted #666;
        min-width: 100px;
        height: 20px;
      }

      /* Checkboxes */
      .checkbox-row {
        display: flex;
        gap: 20px;
        margin-top: 10px;
        font-size: 14px;
        flex-wrap: wrap;
      }

      .checkbox-item {
        display: flex;
        align-items: center;
        gap: 5px;
      }

      .checkbox-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #333;
      }

      /* Grades Table */
      .grades-section {
        border: 0px solid #333;
        margin: 15px 0;
      }

      .grades-title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        padding: 8px;
        /* border-bottom: 2px solid #333; */
        text-decoration: underline;
        text-underline-offset: 5px;
      }

      .grades-table {
        width: 100%;
        border-collapse: collapse;
      }

      .grades-table th,
      .grades-table td {
        border: 1px solid #333;
        padding: 8px 10px;
        text-align: center;
        font-size: 13px;
      }

      .grades-table th {
        background: #f5f5f5;
        font-weight: bold;
      }

      .grades-table td {
        height: 30px;
      }

      .grades-table .subject-col {
        width: 120px;
        font-weight: bold;
      }

      .grades-table .total-col,
      .grades-table .average-col {
        width: 70px;
        font-weight: bold;
      }

      /* Footer signatures */
      .footer-signatures {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        font-size: 14px;
        text-align: center;
        direction: rtl;
      }

      .footer-sig {
        text-align: center;
      }

      .footer-sig .name {
        font-weight: bold;
        margin-bottom: 5px;
      }

      .footer-sig .title {
        font-size: 12px;
        font-weight: bold;
      }

      /* Requirements footer */
      .requirements {
        margin-top: 15px;
        font-size: 11px;
        line-height: 1.8;
        border-top: 1px solid #ccc;
        padding-top: 8px;
        text-align: right;
      }

      @media print {
        body {
          background: white;
          padding: 0;
        }
        .page {
          width: 100%;
          box-shadow: none;
          padding: 10mm;
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
          <img src="{{ asset('images/yemen.logo.png') }}" alt="شعار الجمهورية اليمنية">
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
              <img src="{{ asset('storage/' . $certificate->student_image) }}" style="width:120px;height:150px;object-fit:cover;" alt="صورة الطالب">
            @else
              <div class="photo-placeholder">صورة<br />الطالب</div>
            @endif
            <div class="photo-label">صورة الطالب</div>
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
          <div class="dots">{{ $student->full_name ?? '' }}</div>
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
                <th>قران</th>
                <th>عربي</th>
                <th>انجليزي</th>
                <th>رياضيات</th>
                <th>فيزياء</th>
                <th>كيمياء</th>
                <th>احياء</th>
                <th>موسيقى</th>
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
