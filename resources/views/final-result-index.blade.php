<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>النتائج النهائية للطلاب</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        .main-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-top: 2rem;
            overflow: hidden;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
        }

        .page-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
            text-align: center;
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stats-card.danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .stats-card p {
            margin: 0.5rem 0 0 0;
            font-size: 1rem;
            opacity: 0.9;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .action-buttons {
            margin-bottom: 1.5rem;
        }

        .btn-custom {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
        }

        .table-responsive {
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-radius: 10px;
            /* overflow: hidden; */
        }

        .table thead th {
            vertical-align: middle;
            background: linear-gradient(135deg, #434343 0%, #000000 100%);
            color: #fff;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }

        .subject-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .badge {
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 20px;
        }

        .pagination {
            margin-top: 1.5rem;
        }

        .pagination .page-link {
            color: #667eea;
            border-radius: 5px;
            margin: 0 0.2rem;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="container-fluid px-4">
    <div class="main-container">
        <!-- Header -->
        <div class="page-header">
            <h1><i class="fas fa-graduation-cap me-2"></i>النتائج النهائية للطلاب</h1>
            <p>نظرة شاملة على أداء الطلاب للعام الدراسي</p>
        </div>

        <div class="p-4">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3>{{ $stats['total'] ?? 0 }}</h3>
                        <p>إجمالي الطلاب</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card success">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h3>{{ $stats['passed'] ?? 0 }}</h3>
                        <p>الطلاب الناجحون</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card danger">
                        <i class="fas fa-times-circle fa-2x mb-2"></i>
                        <h3>{{ $stats['failed'] ?? 0 }}</h3>
                        <p>الطلاب الراسبون</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <a href="{{ route('import.form') }}" class="btn btn-primary-custom btn-custom">
                        <i class="fas fa-file-upload me-2"></i>استيراد النتائج
                    </a>
                </div>
                <div>
                    <button type="button" class="btn btn-success-custom btn-custom" onclick="showExportModal()">
                        <i class="fas fa-file-excel me-2"></i>تصدير إلى Excel
                    </button>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filter-section">
                <form method="GET" action="{{ url('/final-results') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="academic_year_id" class="form-label fw-bold">
                                <i class="fas fa-calendar me-1"></i>السنة الدراسية
                            </label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="class_id" class="form-label fw-bold">
                                <i class="fas fa-school me-1"></i>الصف
                            </label>
                            <select class="form-select" id="class_id" name="class_id" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                @foreach ($schoolClasses as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="final_result" class="form-label fw-bold">
                                <i class="fas fa-award me-1"></i>النتيجة
                            </label>
                            <select class="form-select" id="final_result" name="final_result" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                <option value="ناجح" {{ request('final_result') == 'ناجح' ? 'selected' : '' }}>ناجح</option>
                                <option value="راسب" {{ request('final_result') == 'راسب' ? 'selected' : '' }}>راسب</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="search" class="form-label fw-bold">
                                <i class="fas fa-search me-1"></i>البحث
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="اسم الطالب أو الرقم المدرسي" value="{{ request('search') }}">
                                <button class="btn btn-primary-custom" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    @if(request()->hasAny(['academic_year_id', 'class_id', 'final_result', 'search']))
                        <div class="mt-3">
                            <a href="{{ url('/final-results') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>إلغاء الفلاتر
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Results Table -->
            @if($finalResults->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>لا توجد نتائج لعرضها</h4>
                    <p class="text-muted">جرب تغيير معايير البحث أو قم باستيراد النتائج</p>
                    <a href="{{ route('import.form') }}" class="btn btn-primary-custom btn-custom mt-3">
                        <i class="fas fa-file-upload me-2"></i>استيراد النتائج الآن
                    </a>
                </div>
            @else
                <div class="table-responsive bg-white">
                    <table class="table table-bordered table-striped text-center mb-0">
                        <thead>
                            <!-- الصف الأول للعناوين الرئيسية -->
                            <tr>
                                <th rowspan="2" class="align-middle">#</th>
                                <th rowspan="2" class="align-middle">اسم الطالب</th>
                                <th rowspan="2" class="align-middle">الصف</th>
                                @foreach ($subjects as $subject)
                                    <th colspan="3" class="subject-header">{{ $subject->name }}</th>
                                @endforeach
                                <th rowspan="2" class="align-middle">المجموع الكلي</th>
                                <th rowspan="2" class="align-middle">النتيجة</th>
                            </tr>
                            <!-- الصف الثاني لعناوين الدرجات -->
                            <tr>
                                @foreach ($subjects as $subject)
                                    <th>ف1</th>
                                    <th>ف2</th>
                                    <th>المجموع</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($finalResults as $result)
                                <tr>
                                    <td>{{ $loop->iteration + $finalResults->firstItem() - 1 }}</td>
                                    <td class="text-nowrap fw-bold">{{ $result->student->full_name }}</td>
                                    <td class="text-nowrap">{{ $result->student->schoolClass->name }}</td>

                                    <!-- المرور على المواد وعرض درجة الطالب في كل مادة -->
                                    @foreach ($subjects as $subject)
                                        @php
                                            $grade = $result->student->grades->firstWhere('subject_id', $subject->id);
                                        @endphp
                                        <td>{{ $grade->first_semester_total ?? '-' }}</td>
                                        <td>{{ $grade->second_semester_total ?? '-' }}</td>
                                        <td><strong>{{ $grade->total ?? '-' }}</strong></td>
                                    @endforeach

                                    <td><strong class="text-primary">{{ $result->total_student_grades }}</strong></td>
                                    <td>
                                        @if($result->final_result == 'ناجح')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>{{ $result->final_result }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>{{ $result->final_result }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $finalResults->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تصدير النتائج إلى Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('final-result.export') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export_class_name" class="form-label">اسم الصف</label>
                        <input type="text" class="form-control" id="export_class_name" name="class_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="export_academic_year" class="form-label">السنة الدراسية</label>
                        <select class="form-select" id="export_academic_year" name="academic_year_id" required>
                            <option value="">-- اختر السنة الدراسية --</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success-custom">
                        <i class="fas fa-download me-2"></i>تصدير
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showExportModal() {
        const modal = new bootstrap.Modal(document.getElementById('exportModal'));
        modal.show();
    }
</script>
</body>
</html>
