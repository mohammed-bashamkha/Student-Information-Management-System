<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استيراد النتائج النهائية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style> body { background-color: #f8f9fa; } .card { border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.1); } </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">استيراد النتائج النهائية للطلاب</h3>
                </div>
                <div class="card-body p-4">

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        الرجاء اختيار العام الدراسي ثم رفع ملف Excel. سيتم تحديد الصف لكل طالب تلقائياً من بيانات الملف.
                    </p>

                    <form action="{{ route('import.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="academicYear" class="form-label fw-bold">العام الدراسي</label>
                            <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academicYear" name="academic_year_id" required>
                                <option selected disabled value="">-- اختر العام الدراسي --</option>
                                @foreach ($academicYears as $year)
                                    {{-- التعديل هنا: استخدام 'year' بدلاً من 'name' --}}
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">ملف النتائج (Excel)</label>
                            <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file" required accept=".xlsx, .xls, .csv">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">رفع واستيراد النتائج</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
