<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Inquiry - MySebenarnya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    @include('layouts.sidebarPublic')
    
    <!-- Main content area with proper positioning -->
    <div class="main-content">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">add_circle</i>
                                    Create New Inquiry
                                </h3>
                            </div>                            <div class="card-body">
                                <p class="text-muted mb-4">
                                </p>

                                <!-- Display Validation Errors -->
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <strong>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('inquiry.store') }}" method="POST" enctype="multipart/form-data" id="inquiryForm">
                                    @csrf
                                    
                                    <!-- News Title -->
                                    <div class="mb-4">
                                        <label for="news_title" class="form-label">
                                            <strong>News Title</strong> <span class="text-danger">*</span>
                                        </label>                                        <input type="text" 
                                               class="form-control form-control-lg @error('news_title') is-invalid @enderror" 
                                               id="news_title" 
                                               name="news_title" 
                                               value="{{ old('news_title') }}"
                                               placeholder="Enter the title"
                                               maxlength="30"
                                               required>
                                        <div class="form-text">
                                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">info</i>
                                            Provide the title (Max: 30 characters)
                                        </div>
                                        @error('news_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Detailed Information -->
                                    <div class="mb-4">
                                        <label for="detailed_info" class="form-label">
                                            <strong>Detailed Information</strong> <span class="text-danger">*</span>
                                        </label>                                        <textarea class="form-control @error('detailed_info') is-invalid @enderror" 
                                                  id="detailed_info" 
                                                  name="detailed_info" 
                                                  rows="6"
                                                  placeholder="Provide detailed information about the news:
- What is the news about?
- Where did you see/hear this news?"
                                                  maxlength="250"
                                                  required>{{ old('detailed_info') }}</textarea>
                                        <div class="form-text">
                                            <i class="material-icons" style="font-size: 14px; vertical-align: middle;">info</i>
                                            Please provide details in question (Max: 250 characters)
                                        </div>
                                        @error('detailed_info')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>                                    <!-- Supporting Evidence Files -->
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <strong>Evidence - Files</strong> <span class="text-danger">*</span>
                                        </label>
                                        <div class="border rounded p-4 bg-light">
                                            <div id="file-upload-area">
                                                <input type="file" 
                                                       class="form-control mb-3" 
                                                       name="evidence_files[]"                                                       accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt,.mp4,.mp3"
                                                       multiple
                                                       required>
                                            </div>
                                        </div>@error('evidence_files.*')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror                                    </div>

                                    <!-- Supporting Evidence Links -->
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <strong>Evidence - Links</strong> <span class="text-muted">(Optional)</span>
                                        </label>                                        <div class="border rounded p-4 bg-light">
                                            <div class="link-input-group mb-3">
                                                <label class="form-label small">Original News Link:</label>
                                                <input type="url" 
                                                       class="form-control" 
                                                       name="evidence_links" 
                                                       value="{{ old('evidence_links') }}" 
                                                       placeholder="https://example.com/" 
                                                       maxlength="500">
                                            </div>
                                        </div>
                                        @error('evidence_links.*')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Terms and Conditions -->
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terms" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                                            <label class="form-check-label small" for="terms">
                                                I confirm that the information provided is accurate. <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                        @error('terms')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    

                                    <!-- Action Buttons -->
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3 border-top">
                                        <a href="{{ route('module3.status') }}" class="btn btn-secondary btn-lg me-md-2">
                                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">cancel</i>
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                            <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">send</i>
                                            Submit Inquiry
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Ensure proper spacing for sidebar layout */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-content {
            margin-left: 250px; /* Account for sidebar width */
            padding-top: 70px;  /* Account for top bar height */
            min-height: 100vh;
        }
        
        .content {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }
        
        .display-4 {
            font-size: 2.5rem;
            font-weight: 300;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .material-icons {
            font-size: 24px;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }
            
            .content {
                padding: 15px;
            }
            
            .display-4 {
                font-size: 2rem;
            }
        }
    </style>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>        document.addEventListener('DOMContentLoaded', function() {
            // Form submission handling
            const form = document.getElementById('inquiryForm');
            const submitBtn = document.getElementById('submit-btn');
            
            form.addEventListener('submit', function(e) {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="material-icons" style="vertical-align: middle; margin-right: 8px;">hourglass_empty</i>Submitting...';
                  // Basic validation
                const newsTitle = document.getElementById('news_title').value.trim();
                const detailedInfo = document.getElementById('detailed_info').value.trim();
                const evidenceFiles = document.querySelector('input[name="evidence_files[]"]').files;
                const terms = document.getElementById('terms').checked;
                
                if (!newsTitle || !detailedInfo || evidenceFiles.length === 0 || !terms) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="material-icons" style="vertical-align: middle; margin-right: 8px;">send</i>Submit Inquiry';
                    
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.innerHTML = '<strong>Error:</strong> Please fill in all required fields and upload at least one evidence file.';
                    
                    // Remove existing error messages
                    const existingError = form.querySelector('.alert-danger');
                    if (existingError && !existingError.querySelector('ul')) {
                        existingError.remove();
                    }
                    
                    form.insertBefore(errorDiv, form.firstChild);
                    
                    // Scroll to top to show error
                    document.querySelector('.main-content').scrollTop = 0;
                    
                    return false;
                }            });
            
            // Character count for text areas
            const detailedInfoTextarea = document.getElementById('detailed_info');
            const newsTitle = document.getElementById('news_title');
              // Add character counters
            addCharacterCounter(newsTitle, 30);
            addCharacterCounter(detailedInfoTextarea, 250);
              function addCharacterCounter(element, maxLength) {
                const counter = document.createElement('div');
                counter.className = 'form-text text-end';
                counter.style.fontSize = '0.8em';
                element.parentNode.appendChild(counter);
                
                function updateCounter() {
                    const remaining = maxLength - element.value.length;
                    const percentage = (element.value.length / maxLength) * 100;
                    counter.textContent = `${element.value.length}/${maxLength} characters`;
                    
                    // Only turn red when approaching the limit (90% or more used)
                    counter.style.color = percentage >= 90 ? '#dc3545' : '#6c757d';
                }
                  element.addEventListener('input', updateCounter);
                updateCounter();
            }});
        
        // File upload preview
        document.querySelector('input[name="evidence_files[]"]').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('file-preview');
            
            if (preview) {
                preview.remove();
            }
            
            if (files.length > 0) {
                const previewDiv = document.createElement('div');
                previewDiv.id = 'file-preview';
                previewDiv.className = 'mt-3';
                
                let previewHTML = '<strong>Selected Files:</strong><ul class="list-unstyled mt-2">';
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    const fileIcon = getFileIcon(file.type);
                    
                    previewHTML += `
                        <li class="d-flex align-items-center mb-1">
                            <i class="material-icons me-2">${fileIcon}</i>
                            <span class="me-2">${file.name}</span>
                            <small class="text-muted">(${fileSize} MB)</small>
                        </li>
                    `;
                }
                
                previewHTML += '</ul>';
                previewDiv.innerHTML = previewHTML;
                
                e.target.parentNode.appendChild(previewDiv);
            }
        });
        
        function getFileIcon(mimeType) {
            if (mimeType.startsWith('image/')) return 'image';
            if (mimeType.includes('pdf')) return 'picture_as_pdf';
            if (mimeType.includes('document') || mimeType.includes('word')) return 'description';
            if (mimeType.startsWith('video/')) return 'video_file';
            return 'attach_file';
        }
    </script>
</body>
</html>