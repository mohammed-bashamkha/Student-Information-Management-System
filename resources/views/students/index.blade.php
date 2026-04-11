<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة الطلاب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
        
        body { 
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .main-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header-custom h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .btn-light-custom {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            text-decoration: none;
        }
        
        .btn-light-custom:hover {
            background: white;
            color: #764ba2;
        }

        .table-custom th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 700;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem;
            white-space: nowrap;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 1rem;
            color: #333;
            border-bottom: 1px solid #f1f3f5;
        }

        .table-custom tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-custom {
            padding: 0.5em 0.8em;
            border-radius: 6px;
            font-weight: 600;
        }

        .badge-male { background-color: #e0f2fe; color: #0284c7; }
        .badge-female { background-color: #fce7f3; color: #db2777; }

        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: none;
            transition: all 0.2s;
            color: white;
            text-decoration: none;
            margin-right: 0.25rem;
        }

        .btn-edit { background-color: #f59e0b; }
        .btn-edit:hover { background-color: #d97706; color: white; }
        
        .btn-delete { background-color: #ef4444; }
        .btn-delete:hover { background-color: #dc2626; color: white; }

        .btn-view { background-color: #3b82f6; }
        .btn-view:hover { background-color: #2563eb; color: white; }

        /* Pagination Customization */
        .pagination { margin-bottom: 0; }
        .page-link { color: #667eea; border-radius: 5px; margin: 0 3px; }
        .page-item.active .page-link { background-color: #667eea; border-color: #667eea; }
        
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="main-card">
                <div class="card-header-custom flex-column flex-md-row gap-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-graduate fs-3 me-3 ms-2"></i>
                        <div>
                            <h3>سجل الطلاب</h3>
                            <p class="mb-0 text-white-50 mt-1 fs-6">إدارة وعرض بيانات الطلاب المسجلين</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('students.import.form') }}" class="btn btn-light-custom shadow-sm">
                            <i class="fas fa-file-import me-2"></i>استيراد طلاب (Excel)
                        </a>
                    </div>
                </div>
                
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>رقم الطالب</th>
                                    <th>رقم الجلوس</th>
                                    <th>الاسم الرباعي</th>
                                    <th>الجنس</th>
                                    <th>تاريخ الميلاد</th>
                                    <th>المدرسة</th>
                                    <th>الصف</th>
                                    <th>السنة الدراسية</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
    @php
        // جلب بيانات التسجيل الحالي لتسهيل القراءة في الكود
        $enrollment = $student->currentEnrollment;
    @endphp
    <tr>
        <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
        <td><span class="fw-bold text-muted">{{ $student->school_number ?? '-' }}</span></td>
        <td><span class="fw-bold text-muted">{{ $student->seat_number ?? '-' }}</span></td>
        <td class="fw-bold">{{ $student->full_name }}</td>
        <td>
            @if($student->gender == 'male')
                <span class="badge badge-custom badge-male"><i class="fas fa-male me-1"></i>ذكر</span>
            @elseif($student->gender == 'female')
                <span class="badge badge-custom badge-female"><i class="fas fa-female me-1"></i>أنثى</span>
            @else
                <span class="badge bg-secondary badge-custom">{{ $student->gender ?? '-' }}</span>
            @endif
        </td>
        <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y/m/d') : '-' }}</td>
        
        <td>{{ $enrollment->school->name ?? 'غير محدد' }}</td>
        <td>{{ $enrollment->schoolClass->name ?? 'غير محدد' }}</td>
        <td>
            <span class="badge bg-light text-dark border">
                {{ $enrollment->academicYear->year ?? '----' }}
            </span>
        </td>
        
        <td><small class="text-muted">{{ $student->created_at->format('Y/m/d') }}</small></td>
        <td>
            <div class="d-flex">
                <a href="#" class="action-btn btn-view" title="عرض التفاصيل"><i class="fas fa-eye"></i></a>
                <a href="#" class="action-btn btn-edit" title="تعديل"><i class="fas fa-edit"></i></a>
                <button type="button" class="action-btn btn-delete" title="حذف" 
                        onclick="return confirm('هل أنت متأكد؟')">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($students->hasPages())
                    <div class="card-footer bg-white border-top p-4 d-flex justify-content-center">
                        {{ $students->links('pagination::bootstrap-5') }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>