<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استيراد النتائج النهائية</title>
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
            padding: 2rem;
            text-align: center;
        }
        
        .card-header-custom h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
        }
        
        .card-header-custom p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .report-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .report-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .stat-box {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-box h4 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-box p {
            margin: 0.25rem 0 0 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .stat-box.success h4 {
            color: #28a745;
        }
        
        .stat-box.danger h4 {
            color: #dc3545;
        }
        
        .stat-box.primary h4 {
            color: #667eea;
        }
        
        .error-item, .warning-item {
            background: white;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            border-right: 4px solid #dc3545;
        }
        
        .warning-item {
            border-right-color: #ffc107;
        }
        
        .progress-custom {
            height: 25px;
            border-radius: 12px;
            background: #e9ecef;
        }
        
        .progress-bar-custom {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 12px;
            font-weight: 600;
        }
        
        .file-upload-area {
            border: 3px dashed #667eea;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            background: #f8f9fa;
            border-color: #764ba2;
        }
        
        .file-upload-area i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="main-card">
                <div class="card-header-custom">
                    <h3><i class="fas fa-file-upload me-2"></i>استيراد النتائج النهائية للطلاب</h3>
                    <p>قم برفع ملف Excel يحتوي على النتائج النهائية</p>
                </div>
                
                <div class="p-4">
                    <!-- Success Messages -->
                    @if (session('success'))
                        <div class="alert alert-success alert-custom" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    <!-- Warning Messages -->
                    @if (session('warning'))
                        <div class="alert alert-warning alert-custom" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                        </div>
                    @endif

                    <!-- Error Messages -->
                    @if (session('error'))
                        <div class="alert alert-danger alert-custom" role="alert">
                            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-custom" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>يرجى تصحيح الأخطاء التالية:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Import Report -->
                    @if (session('import_report'))
                        @php
                            $report = session('import_report');
                        @endphp
                        <div class="report-section">
                            <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>تقرير الاستيراد</h5>
                            
                            <!-- Statistics -->
                            <div class="report-stats">
                                <div class="stat-box primary">
                                    <h4>{{ $report['summary']['total_rows'] }}</h4>
                                    <p>إجمالي السجلات</p>
                                </div>
                                <div class="stat-box success">
                                    <h4>{{ $report['summary']['successful'] }}</h4>
                                    <p>ناجح</p>
                                </div>
                                <div class="stat-box danger">
                                    <h4>{{ $report['summary']['failed'] }}</h4>
                                    <p>فاشل</p>
                                </div>
                                <div class="stat-box">
                                    <h4>{{ $report['summary']['students_created'] }}</h4>
                                    <p>طلاب جدد</p>
                                </div>
                                <div class="stat-box">
                                    <h4>{{ $report['summary']['students_updated'] }}</h4>
                                    <p>طلاب محدّثون</p>
                                </div>
                                <div class="stat-box" style="border-top: 3px solid #667eea;">
                                    <h4 style="color:#667eea;">{{ $report['summary']['enrollments_created'] ?? 0 }}</h4>
                                    <p>تسجيلات جديدة</p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold">نسبة النجاح</span>
                                    <span class="fw-bold">{{ $report['summary']['success_rate'] }}%</span>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar progress-bar-custom" 
                                         style="width: {{ $report['summary']['success_rate'] }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Errors -->
                            @if(count($report['errors']) > 0)
                                <div class="mt-3">
                                    <h6 class="text-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        الأخطاء ({{ count($report['errors']) }})
                                    </h6>
                                    <div style="max-height: 300px; overflow-y: auto;">
                                        @foreach($report['errors'] as $error)
                                            <div class="error-item">
                                                <strong>الصف {{ $error['row'] }}:</strong> {{ $error['message'] }}
                                                <br>
                                                <small class="text-muted">
                                                    رقم الطالب: {{ $error['data']['student_number'] }} - 
                                                    الاسم: {{ $error['data']['student_name'] }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Warnings -->
                            @if(count($report['warnings']) > 0)
                                <div class="mt-3">
                                    <h6 class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        التحذيرات ({{ count($report['warnings']) }})
                                    </h6>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        @foreach($report['warnings'] as $warning)
                                            <div class="warning-item">
                                                <small>{{ $warning }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mb-4">
                        <a href="{{ url('/final-results') }}" class="btn btn-outline-custom">
                            <i class="fas fa-eye me-2"></i>عرض النتائج
                        </a>
                    </div>

                    <hr class="my-4">

                    <!-- Import Form -->
                    <form action="{{ route('import.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="academicYear" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>السنة الدراسية
                                </label>
                                <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                        id="academicYear" name="academic_year_id" required>
                                    <option selected disabled value="">-- اختر السنة الدراسية --</option>
                                    @foreach ($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="school_class" class="form-label">
                                    <i class="fas fa-school me-1"></i>الصف
                                </label>
                                <select class="form-select @error('class_id') is-invalid @enderror" 
                                        id="school_class" name="class_id" required>
                                    <option selected disabled value="">-- اختر الصف الدراسي --</option>
                                    @foreach ($school_classes as $school_class)
                                        <option value="{{ $school_class->id }}" {{ old('class_id') == $school_class->id ? 'selected' : '' }}>
                                            {{ $school_class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="school" class="form-label">
                                    <i class="fas fa-building me-1"></i>المدرسة
                                </label>
                                <select class="form-select @error('school_id') is-invalid @enderror" 
                                        id="school" name="school_id" required>
                                    <option selected disabled value="">-- اختر المدرسة --</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="file" class="form-label">
                                <i class="fas fa-file-excel me-1"></i>ملف النتائج (Excel)
                            </label>
                            <div class="file-upload-area" onclick="document.getElementById('file').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p class="mb-0 fw-bold">انقر لاختيار ملف Excel</p>
                                <small class="text-muted">أو اسحب الملف وأفلته هنا</small>
                                <p class="mt-2 mb-0">
                                    <small class="text-muted">الصيغ المدعومة: .xlsx, .xls, .csv</small>
                                </p>
                            </div>
                            <input class="form-control d-none @error('file') is-invalid @enderror" 
                                   type="file" id="file" name="file" required 
                                   accept=".xlsx, .xls, .csv"
                                   onchange="updateFileName(this)">
                            <small id="fileName" class="text-muted mt-2 d-block"></small>
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info alert-custom">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>ملاحظة:</strong> تأكد من أن ملف Excel يحتوي على الأعمدة الصحيحة وأن البيانات تبدأ من الصف الخامس.
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-file-upload me-2"></i>رفع واستيراد النتائج
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateFileName(input) {
        const fileName = input.files[0]?.name;
        const fileNameDisplay = document.getElementById('fileName');
        if (fileName) {
            fileNameDisplay.innerHTML = '<i class="fas fa-file-excel me-1"></i>' + fileName;
            fileNameDisplay.classList.add('text-success', 'fw-bold');
        }
    }
    
    // Drag and drop functionality
    const fileUploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('file');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, () => {
            fileUploadArea.style.background = '#f8f9fa';
        }, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, () => {
            fileUploadArea.style.background = '';
        }, false);
    });
    
    fileUploadArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        updateFileName(fileInput);
    }, false);
</script>
</body>
</html>
