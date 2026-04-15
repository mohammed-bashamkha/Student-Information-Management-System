<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب استخراج بدلاً عن فاقد - نسخة المكتب</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Amiri', 'Cairo', 'Traditional Arabic', serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .page {
            width: 210mm;
            min-height: 265mm;
            background: white;
            padding: 10mm 20mm 10mm 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
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

        /* Addressed To Section */
        .addressed-to {
            display: flex;
            justify-content: flex-start;
            align-items: baseline;
            font-size: 18px;
            margin: 20px 0 5px;
            gap: 10px;
            line-height: 1.8;
        }

        .addressed-to .bold {
            font-weight: bold;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 15px;
        }

        /* Main Title */
        .main-title {
            text-align: center;
            font-size: 26px;
            font-weight: bold;
            margin: 15px 0 20px;
            text-decoration: underline;
            text-underline-offset: 8px;
        }

        /* Body Text */
        .body-text {
            font-size: 18px;
            line-height: 2;
            text-align: justify;
            margin-bottom: 15px;
        }

        .body-text .bold-underline {
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 5px;
        }

        .body-text .bold {
            font-weight: bold;
        }

        /* Student Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
        }

        .info-table th,
        .info-table td {
            border: 1px solid #333;
            padding: 10px 15px;
            text-align: center;
        }

        .info-table th {
            background: #f5f5f5;
            font-weight: bold;
        }

        /* Conclusion text */
        .conclusion {
            font-size: 18px;
            line-height: 2;
            text-align: justify;
            margin: 20px 0;
        }

        .closing {
            text-align: center;
            font-size: 18px;
            margin: 20px 0 30px;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            font-size: 15px;
            text-align: center;
            direction: rtl;
        }

        .sig-block {
            text-align: center;
        }

        .sig-block .name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .sig-block .title {
            font-weight: bold;
            font-size: 14px;
        }

        /* Footer */
        .page-footer {
            margin-top: auto;
            border-top: 2px solid #333;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: bold;
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
                <img src="{{ public_path('images/yemen.logo.png') }}" alt="شعار الجمهورية اليمنية">
            </div>
            <div class="header-left">
                <div>رقم النموذج: <span>{{ $certificate->id }}</span></div>
                <div>التاريخ: <span>{{ $printDate }}</span></div>
            </div>
        </div>

        <!-- Addressed To -->
        <div class="addressed-to">
            <span class="bold">الأخ / مدير عام مكتب وزارة التربية و التعليم م / حضرموت</span>
            <span>المحترم</span>
        </div>
        <div class="greeting">تحية طيبة . . .</div>

        <!-- Main Title -->
        <div class="main-title">الموضوع / استخراج بدلاً عن فاقد</div>

        <!-- Body Paragraph 1 -->
        <div class="body-text">
            نهديكم أطيب تحياتنا متمنين لكم التوفيق والنجاح في مهامكم التربوية والتعليمية الجليلة . .
            <br>
            اشارة إلى الموضوع أعلاه وبناءاً على خطاب مدير مدرسة <span class="bold-underline">{{ $school->name ?? '...' }}</span> بطلب
            <br>
            استخراج شهادة <span class="bold-underline">{{ $certificate->certificate_type ?? 'انهاء المرحلة الثانوية' }}</span> بدلاً عن فاقد للطالب المذكور ادناه بحسب البيانات التالية :
        </div>

        <!-- Student Info Table -->
        <table class="info-table">
            <thead>
                <tr>
                    <th>أسم الطالب</th>
                    <th>المدرسـة</th>
                    <th>رقم الجلوس</th>
                    <th>الصف</th>
                    <th>العام الدراسي</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $student->full_name ?? '' }}</td>
                    <td>{{ $school->name ?? '' }}</td>
                    <td>{{ $student->seat_number ?? '' }}</td>
                    <td>{{ $schoolClass->name ?? '' }}</td>
                    <td>{{ $academicYear->year ?? '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Body Paragraph 2 -->
        <div class="conclusion">
            نرفع الموضوع إليكم لاستكمال اجراءات استخراج الشهادة المذكورة بحسب البيانات أعلاه . . .
            <br>
            نرفق لكم استمارة الطلب  ورسالة مدير المدرسة المذكوره أعلاه  وصورة اثبات الهوية معمدة طبق الاصل
            <br>
            وصور شمسية .
        </div>

        <!-- Closing -->
        <div class="closing">ولكم خالص الشكر و التقدير،،،</div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-block">
                <div class="name">{{ $createdBy->name ?? '' }}</div>
                <div class="title">المختص</div>
            </div>
            <div class="sig-block">
                <div class="name"></div>
                <div class="title">رئيس قسم الاختبارات</div>
            </div>
            <div class="sig-block">
                <div class="name"></div>
                <div class="title">مدير إدارة التربية و التعليم م / المكلا</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="page-footer">
            <div>طبع بواسطة {{ $createdBy->name ?? '' }}</div>
            <div>مكتب التربية والتعليم مديرية المكلا</div>
            <div>{{ $printDate }}</div>
        </div>
    </div>
</body>
</html>
