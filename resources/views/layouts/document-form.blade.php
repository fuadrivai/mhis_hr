<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo Approval System</title>
    <link href="/plugins/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    
</head>
<body>
    <div class="notification" id="successNotification">
        <i class="fas fa-check-circle text-success"></i>
        <div>
            <strong>Success!</strong><br>
            <span>Your document has been submitted for approval.</span>
        </div>
    </div>

    <div class="container card-container">
        <div class="card main-card">
            <div class="card-header">
                <div class="header-decoration"></div>
                <div class="header-decoration-2"></div>
                <div class="header-decoration-3"></div>
                <div class="header-decoration-4"></div>
                
                <div class="header-content">
                    <div class="logo-container">
                        <div class="logo-wrapper">
                            <img src="https://mutiaraharapan.sch.id/wp-content/uploads/2017/01/logo-bintaro.png" alt="Company Logo" class="logo">
                        </div>
                    </div>
                    
                    <div class="title-container">
                        <h1 class="card-title">Document Approval System</h1>
                        <p class="card-subtitle">Efficiently manage and track your document approval workflow</p>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- User Information Section -->
                <div class="user-info-section">
                    <h3 class="user-info-title"><i class="fas fa-user-circle"></i>User Information</h3>
                    <div class="info-row">
                        <div class="info-label"><i class="fa fa-user"></i>Name:</div>
                        <div class="info-value">{{$data->personal->fullname}}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fa fa-envelope"></i>Email:</div>
                        <div class="info-value">{{$data->personal->email}}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fa fa-building"></i>Branch:</div>
                        <div class="info-value">{{$data->employment->branch_name}}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fa fa-puzzle-piece"></i>Division:</div>
                        <div class="info-value">{{$data->employment->organization_name}}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fa fa-sitemap"></i>Position:</div>
                        <div class="info-value">{{$data->employment->job_position_name}}</div>
                    </div>
                </div>
                
                <!-- Form Section -->
                <h3 class="form-section-title"><i class="fas fa-file-alt"></i>Document Details</h3>
                <form id="memoForm" action="/internal-document" method="POST">
                    @csrf
                    <!-- Type Selection -->
                    <div class="form-group">
                        <label class="d-block mb-3"><i class="fa fa-tag"></i>Type <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio custom-control-inline ml-3">
                            <input type="radio" id="typeMemo" name="type" class="custom-control-input" value="Memo" checked>
                            <label class="custom-control-label" for="typeMemo">Memo</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="typeActionPlan" name="type" class="custom-control-input" value="Action Plan">
                            <label class="custom-control-label" for="typeActionPlan">Action Plan</label>
                        </div>
                    </div>
                    
                    <!-- Document Title -->
                    <div class="form-group">
                        <label for="title"><i class="fa fa-file-text"></i>Document Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter document title" required>
                    </div>
                    
                    <!-- Document Link -->
                    <div class="form-group">
                        <label for="link"><i class="fa fa-link"></i>Document Link <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="link" name="link" placeholder="https://example.com/document" required>
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes"><i class="fa fa-sticky-note"></i>Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any additional notes or comments"></textarea>
                        <small class="form-text text-muted mt-2"><i class="fa fa-info-circle mr-1"></i>Optional: Provide context or special instructions for approvers.</small>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-submit">
                        <i class="fa fa-paper-plane mr-2"></i>Submit for Approval
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="/plugins/jquery/dist/jquery.min.js"></script>
    <script src="/plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
     <script src="/plugins/select2/dist/js/select2.full.min.js"></script>
     <script src="/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="/plugins/bootstrap-datepicker/js/jquery.timepicker.min.js"></script>
    <script src="/plugins/jquery.blockUI/jquery.blockUI.js"></script>
    <script src="/js/script.js?v=1.1.5"></script>
    
    <!-- Custom jQuery Script -->
    <script>
        $(document).ready(function() {
            $('#memoForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const formData = form.serialize();

                ajax(formData, form.attr('action'), form.attr('method'),
                    function(item) {
                        // Show success notification
                        $('#successNotification').addClass('show');
                        setTimeout(function() {
                            $('#successNotification').removeClass('show');
                        }, 5000);
                        
                        // Reset form
                        $('#memoForm')[0].reset();
                        $('#typeMemo').prop('checked', true);
                        
                        // Add a subtle confetti effect
                        createConfetti();
                    },
                    function(json) {
                        // Show error notification
                        const errorNotification = $('<div class="notification" style="border-left-color: #e74c3c;"><i class="fas fa-exclamation-circle text-danger"></i><div><strong>Error!</strong><br><span>Please fill in all required fields.</span></div></div>');
                        $('body').append(errorNotification);
                        errorNotification.addClass('show');
                        
                        setTimeout(function() {
                            errorNotification.removeClass('show');
                            setTimeout(function() {
                                errorNotification.remove();
                            }, 2000);
                        }, 4000);
                    }
                )
                
            });
            
            // Add hover effects to form controls
            $('.form-control').hover(
                function() {
                    $(this).css('border-color', '#bdc3c7');
                },
                function() {
                    if (!$(this).hasClass('is-invalid')) {
                        $(this).css('border-color', '#e1e5eb');
                    }
                }
            );
            
            // Add focus effect to radio buttons
            $('.custom-control-input').focus(function() {
                $(this).closest('.custom-control').addClass('focus');
            }).blur(function() {
                $(this).closest('.custom-control').removeClass('focus');
            });
            
            // Confetti effect function
            function createConfetti() {
                const colors = ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6'];
                const confettiCount = 50;
                
                for (let i = 0; i < confettiCount; i++) {
                    const confetti = $('<div class="confetti"></div>');
                    const color = colors[Math.floor(Math.random() * colors.length)];
                    const size = Math.random() * 10 + 5;
                    const left = Math.random() * 100;
                    const animationDuration = Math.random() * 3 + 2;
                    
                    confetti.css({
                        'position': 'fixed',
                        'width': size + 'px',
                        'height': size + 'px',
                        'background': color,
                        'top': '-50px',
                        'left': left + '%',
                        'opacity': 1,
                        'border-radius': '2px',
                        'z-index': 9999,
                        'pointer-events': 'none'
                    });
                    
                    $('body').append(confetti);
                    
                    confetti.animate({
                        top: '100%',
                        left: left + (Math.random() * 40 - 20) + '%',
                        opacity: 0,
                        transform: 'rotate(' + (Math.random() * 360) + 'deg)'
                    }, animationDuration * 1000, function() {
                        $(this).remove();
                    });
                }
            }
        });
    </script>
</body>
</html>