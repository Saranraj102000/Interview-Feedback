<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VDart Candidate Interview Feedback Form</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
    --primary: #2c3e50;
    --secondary: #3498db;
    --accent: #1abc9c; /* Changed from #3498db to #1abc9c to match dashboard */
    --light: #ecf0f1;
    --dark: #34495e;
    --danger: #e74c3c;
    --success: #2ecc71;
    --warning: #f39c12;
    --info: #3498db;
    --border-radius: 0; /* Changed from 8px to 0 */
    --box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Gradient background for body */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            border-top-color: transparent !important; /* Fix for green line */
            border-bottom-color: transparent !important; /* Fix for green line */
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Updated header styling with logo in full left corner */
        header {
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        /* Header container layout */
        .header-full-container {
            width: 100%;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        /* Logo container */
        .logo-container {
            display: flex;
            align-items: center;
            padding-left: 10px;
        }
        
        #company-logo {
            height: 35px;
            margin-left: 0;
            padding-left: 0;
        }
        
        /* Right section with title and navigation */
        .right-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        
        /* Centered header title */
        .header-title {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
            text-align: center;
            width: 100%;
            margin: 0 auto;
        }

        /* Navigation styling */
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-left: 25px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 0;
            transition: all 0.3s ease;
            position: relative;
        }

        nav ul li a:hover {
            color: #fff;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #3498db; /* Changed from var(--accent) to fix green line */
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        .active::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            /* background-color: #3498db;  */
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        /* Main form container */
        .form-container {
            background-color: white;
            padding: 30px;
            margin-top: 30px;
            border-radius: 0; /* Changed from var(--border-radius) to 0 */
            box-shadow: var(--box-shadow);
            border: none !important; /* Fix for green line */
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid var(--light);
            padding-bottom: 20px;
        }

        .form-header h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--dark);
            font-size: 14px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light);
        }

        .section-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 18px;
            padding-left: 10px;
            border-left: 4px solid var(--secondary);
        }

        /* Fix for green line between form fields */
        .form-group {
            margin-bottom: 20px;
            border: none !important;
            border-top: none !important;
            border-bottom: none !important;
            position: relative;
        }

        /* Remove any pseudo-elements that might create lines */
        .form-group::after,
        .form-group::before {
            display: none !important;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
            border-top: none !important; /* Fix for green line */
        }

        /* Ensure consistent form control styling */
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd !important;
            border-radius: 0; /* Changed from var(--border-radius) to 0 */
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Button styling */
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 0; /* Changed from var(--border-radius) to 0 */
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        /* Add question button styling */
        .add-question-btn {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            color: var(--secondary);
            background: none;
            border: 1px dashed var(--secondary);
            padding: 8px 15px;
            border-radius: 0; /* Changed from var(--border-radius) to 0 */
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .add-question-btn:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }

        .add-question-btn i {
            margin-right: 8px;
        }

        /* Question item styling */
        .question-item {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 0;
            margin-bottom: 15px;
            position: relative;
        }

        .question-number {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .remove-question {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .remove-question:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .remove-question span {
            margin-left: 5px;
            font-size: 14px;
        }

        /* Submit section styling */
        .submit-section {
            margin-top: 30px;
            text-align: center;
        }

        .submit-btn {
            padding: 12px 30px;
            font-size: 16px;
        }

        .success-message {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
            padding: 15px;
            border-radius: 0; /* Changed from var(--border-radius) to 0 */
            margin-top: 20px;
            text-align: center;
            display: none;
        }

        /* Checkbox and radio button styling */
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .checkbox-option {
            display: flex;
            align-items: center;
            margin-right: 30px;
        }

        .checkbox-option input[type="radio"] {
            margin-right: 8px;
            cursor: pointer;
        }

        .checkbox-label {
            font-size: 14px;
            cursor: pointer;
        }

        .note {
            font-size: 13px;
            color: #7f8c8d;
            font-style: italic;
            margin-top: 5px;
        }

        /* Multi-step form styling */
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        #step-1 {
            display: block;
        }
        
        /* Error state for form fields */
        .form-control.error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.25);
        }

        /* Success state for form fields */
        .form-control.success {
            border-color: var(--success) !important;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.25);
        }

        /* Question word count styling */
        .question-word-count {
            font-size: 12px;
            margin-top: 5px;
            font-weight: 500;
        }

        .question-word-count.error {
            color: var(--danger) !important;
        }

        .question-word-count.success {
            color: var(--success) !important;
        }

        .question-word-count.neutral {
            color: #6c757d !important;
        }

        /* Required field indicator */
        .text-danger {
            color: var(--danger) !important;
        }

        /* Validation error styling */
        .validation-error {
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 4px solid var(--danger);
            padding: 12px 16px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 14px;
            color: var(--danger);
        }

        /* Validation success styling */
        .validation-success {
            background-color: rgba(40, 167, 69, 0.1);
            border-left: 4px solid var(--success);
            padding: 12px 16px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 14px;
            color: var(--success);
        }

        /* Step navigation styling */
        .step-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--light);
            border-bottom: none !important; /* Fix for green line */
            border-left: none !important; /* Fix for green line */
            border-right: none !important; /* Fix for green line */
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%; /* Kept this as circular for visual indicator */
            background-color: #ddd;
            margin: 0 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            background-color: var(--secondary);
            transform: scale(1.2);
        }

        .step-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .step-title h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .step-title p {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Fix for focus outlines */
        :focus {
            outline-color: var(--secondary);
        }

        /* Modal styling for password */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border: none;
            border-radius: 0;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            font-weight: 700;
            color: #7f8c8d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .close-modal:hover {
            color: var(--danger);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-footer {
            text-align: center;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            /* Mobile header layout */
            .header-full-container {
                flex-direction: row;
                padding: 10px 0;
                position: relative;
            }
            
            .logo-container {
                position: absolute;
                top: 10px;
                left: 10px;
                z-index: 10;
            }
            
            #company-logo {
                height: 30px;
            }
            
            .right-header {
                width: 100%;
                margin-top: 45px; /* Space for the logo above */
                padding: 0 15px;
            }
            
            .header-title {
                text-align: center;
                width: 100%;
                font-size: 20px;
                margin: 0 0 10px 0;
            }
            
            .mobile-menu-btn {
                display: block;
                position: absolute;
                top: 10px;
                right: 15px;
                z-index: 10;
            }
            
            nav {
                width: 100%;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }
            
            nav.open {
                max-height: 200px;
            }
            
            nav ul {
                flex-direction: column;
                width: 100%;
                align-items: center;
            }
            
            nav ul li {
                margin: 10px 0;
                margin-left: 0;
            }
        }

        /* Extra small devices */
        @media (max-width: 576px) {
            .logo-container {
                top: 12px;
                left: 10px;
            }
            
            #company-logo {
                height: 25px;
            }
            
            .header-title {
                font-size: 18px;
            }
            
            .form-header h2 {
                font-size: 20px;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .step-navigation button {
                padding: 10px 15px;
                font-size: 14px;
            }

            .modal-content {
                margin: 30% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <header>
    <div class="header-full-container">
        <!-- Logo container -->
        <div class="logo-container">
            <img id="company-logo" src="vdartwhitelogo (1).png" alt="VDart Logo">
        </div>
        
        <!-- Right section with title and nav -->
        <div class="right-header">
            <h1 class="header-title">VDart Interview Feedback</h1>
            
            <!-- Mobile menu button -->
            <button class="mobile-menu-btn" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav id="main-nav">
                <ul>
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Feedback Form</a></li>
                    <li><a href="#" id="analytics-link" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Analytics</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2>Candidate Interview Feedback Form</h2>
                <p>Please complete the following details after the candidate's interview to capture key feedback and insights.</p>
            </div>

            <div class="step-indicator">
                <div class="step-dot active" data-step="1"></div>
                <div class="step-dot" data-step="2"></div>
                <div class="step-dot" data-step="3"></div>
                <div class="step-dot" data-step="4"></div>
                <div class="step-dot" data-step="5"></div>
            </div>

            <form id="interview-feedback-form">
                <!-- Step 1: Recruiter Information Section -->
                <div class="form-step active" id="step-1">
                    <div class="step-title">
                        <h3>Recruiter Information</h3>
                        <p>Please provide your details</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="employee-id">Employee ID</label>
                        <input type="text" id="employee-id" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="recruiter-name">Recruiter Name</label>
                        <input type="text" id="recruiter-name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="recruiter-email">Email ID</label>
                        <input type="email" id="recruiter-email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="business-unit">Business Unit</label>
                        <select id="business-unit" class="form-control" required>
                            <option value="">Select Business Unit</option>
                            <option value="sidd">Sidd</option>
                            <option value="oliver">Oliver</option>
                            <option value="nambu">Nambu</option>
                            <option value="rohit">Rohit</option>
                            <option value="vinay">Vinay</option>
                            <option value="irfan">Irfan</option>
                        </select>
                    </div>

                    <div class="step-navigation">
                        <div></div> <!-- Empty div for flex spacing -->
                        <button type="button" class="btn btn-primary next-step" data-next="2">Next Step</button>
                    </div>
                </div>

                <!-- Step 2: Interview Details Section -->
                <div class="form-step" id="step-2">
                    <div class="step-title">
                        <h3>Interview Details</h3>
                        <p>Information about the interview</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="interview-date">Date of Interview</label>
                        <input type="date" id="interview-date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Position Interviewed For</label>
                        <input type="text" id="position" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="system-integrator">System Integrator</label>
                        <input type="text" id="system-integrator" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="client-name">Client Name</label>
                        <input type="text" id="client-name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="interviewer-name">Interviewer / Hiring Manager Name</label>
                        <input type="text" id="interviewer-name" class="form-control" required>
                    </div>

                    <!-- NEW FIELDS ADDED HERE -->
                    <div class="form-group">
                        <label for="client-manager-name">Client Manager Name</label>
                        <input type="text" id="client-manager-name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="client-manager-email">Client Manager Email ID/LinkedIn</label>
                        <input type="text" id="client-manager-email" class="form-control" placeholder="Email or LinkedIn profile">
                    </div>

                    <div class="form-group">
                        <label for="geo">GEO</label>
                        <select id="geo" class="form-control" required>
                            <option value="">Select GEO</option>
                            <option value="US">US</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="1">Previous</button>
                        <button type="button" class="btn btn-primary next-step" data-next="3">Next Step</button>
                    </div>
                </div>

                <!-- Step 3: Candidate Information Section -->
                <div class="form-step" id="step-3">
                    <div class="step-title">
                        <h3>Candidate Information</h3>
                        <p>Details about the candidate interviewed</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="candidate-name">Candidate Name</label>
                        <input type="text" id="candidate-name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="candidate-email">Candidate Email</label>
                        <input type="email" id="candidate-email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="applicant-id">Applicant ID</label>
                        <input type="text" id="applicant-id" class="form-control" required>
                    </div>

                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="2">Previous</button>
                        <button type="button" class="btn btn-primary next-step" data-next="4">Next Step</button>
                    </div>
                </div>

                <!-- Step 4: Interview Discussion Section -->
                <div class="form-step" id="step-4">
                    <div class="step-title">
                        <h3>Interview Questions</h3>
                        <p>Key questions asked by the client</p>
                    </div>
                    
                    <div id="questions-container">
                        <!-- Default Question 1 -->
                        <div class="form-group">
                            <label for="question1">Question 1 <span class="text-danger">*</span></label>
                            <textarea id="question1" class="form-control" required placeholder="Enter the interview question (minimum 5 words required)"></textarea>
                            <div class="question-word-count" id="question1-word-count" style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                Word count: 0 (minimum 5 words required)
                            </div>
                        </div>

                        <!-- Default Question 2 -->
                        <div class="form-group">
                            <label for="question2">Question 2</label>
                            <textarea id="question2" class="form-control" placeholder="Enter the interview question (minimum 5 words if filled)"></textarea>
                            <div class="question-word-count" id="question2-word-count" style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                Word count: 0
                            </div>
                        </div>

                        <!-- Default Question 3 -->
                        <div class="form-group">
                            <label for="question3">Question 3</label>
                            <textarea id="question3" class="form-control" placeholder="Enter the interview question (minimum 5 words if filled)"></textarea>
                            <div class="question-word-count" id="question3-word-count" style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                Word count: 0
                            </div>
                        </div>

                        <!-- Default Question 4 -->
                        <div class="form-group">
                            <label for="question4">Question 4</label>
                            <textarea id="question4" class="form-control" placeholder="Enter the interview question (minimum 5 words if filled)"></textarea>
                            <div class="question-word-count" id="question4-word-count" style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                Word count: 0
                            </div>
                        </div>
                    </div>

                    <!-- Additional Questions Section -->
                    <div id="additional-questions"></div>

                    <!-- Add Question Button -->
                    <button type="button" id="add-question-btn" class="add-question-btn">
                        <i class="fas fa-plus"></i> Add Another Question
                    </button>

                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="3">Previous</button>
                        <button type="button" class="btn btn-primary next-step" data-next="5">Next Step</button>
                    </div>
                </div>

                <!-- Step 5: Candidate Reflections and Comments Section -->
                <div class="form-step" id="step-5">
                    <div class="step-title">
                        <h3>Candidate Reflections</h3>
                        <p>Feedback on interview experience</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Did the client inquire about your availability to start?</label>
                        <div class="checkbox-group">
                            <div class="checkbox-option">
                                <input type="radio" id="availability-yes" name="availability" value="yes" required>
                                <label for="availability-yes" class="checkbox-label">Yes</label>
                            </div>
                            <div class="checkbox-option">
                                <input type="radio" id="availability-no" name="availability" value="no">
                                <label for="availability-no" class="checkbox-label">No</label>
                            </div>
                        </div>
                        <p class="note">(If yes, this typically indicates strong client interest.)</p>
                    </div>

                    <div class="form-group">
                        <label>Were you able to answer all questions confidently and clearly?</label>
                        <div class="checkbox-group">
                            <div class="checkbox-option">
                                <input type="radio" id="answer-confidently-yes" name="answer-confidently" value="yes" required>
                                <label for="answer-confidently-yes" class="checkbox-label">Yes</label>
                            </div>
                            <div class="checkbox-option">
                                <input type="radio" id="answer-confidently-no" name="answer-confidently" value="no">
                                <label for="answer-confidently-no" class="checkbox-label">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Were there any questions you found challenging or were unable to answer?</label>
                        <div class="checkbox-group">
                            <div class="checkbox-option">
                                <input type="radio" id="challenging-yes" name="challenging" value="yes" required>
                                <label for="challenging-yes" class="checkbox-label">Yes</label>
                            </div>
                            <div class="checkbox-option">
                                <input type="radio" id="challenging-no" name="challenging" value="no">
                                <label for="challenging-no" class="checkbox-label">No</label>
                            </div>
                        </div>
                    </div>

                    <div id="challenging-questions-section" class="form-group" style="display: none;">
                        <label for="challenging-explanation">If yes, please specify and provide a brief explanation:</label>
                        <textarea id="challenging-explanation" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="additional-comments">Additional Comments (Optional)</label>
                        <p class="note" style="margin-bottom: 10px;">Include any further observations or feedback regarding the interview experience.</p>
                        <textarea id="additional-comments" class="form-control"></textarea>
                    </div>

                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-prev="4">Previous</button>
                        <button type="submit" class="btn btn-success submit-btn">Submit Feedback</button>
                    </div>
                </div>

                <!-- Success Message -->
                <div id="success-message" class="success-message">
                    <i class="fas fa-check-circle"></i> Thank you for your feedback! Your insights will help other candidates prepare better.
                </div>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="password-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-header">
                <h3><i class="fas fa-lock"></i> Authentication Required</h3>
                <p>Please enter your password to access Analytics</p>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <input type="password" id="admin-password" class="form-control" placeholder="Enter password">
                    <p id="password-error" style="color: var(--danger); font-size: 14px; margin-top: 5px; display: none;">
                        <i class="fas fa-exclamation-circle"></i> Incorrect password. Please try again.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="submit-password" class="btn btn-primary">Login</button>
                <button id="cancel-password" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form navigation
            const form = document.getElementById('interview-feedback-form');
            const formSteps = document.querySelectorAll('.form-step');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            const stepDots = document.querySelectorAll('.step-dot');
            const addQuestionBtn = document.getElementById('add-question-btn');
            const additionalQuestionsContainer = document.getElementById('additional-questions');
            const successMessage = document.getElementById('success-message');
            const challengingRadios = document.querySelectorAll('input[name="challenging"]');
            const challengingQuestionsSection = document.getElementById('challenging-questions-section');
            
            // Password modal elements
            const analyticsLink = document.getElementById('analytics-link');
            const passwordModal = document.getElementById('password-modal');
            const closeModalBtn = document.querySelector('.close-modal');
            const submitPasswordBtn = document.getElementById('submit-password');
            const cancelPasswordBtn = document.getElementById('cancel-password');
            const adminPasswordInput = document.getElementById('admin-password');
            const passwordError = document.getElementById('password-error');
            
            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mainNav = document.getElementById('main-nav');
            
            if (mobileMenuToggle && mainNav) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('open');
                    
                    // Change icon between bars and times
                    const icon = mobileMenuToggle.querySelector('i');
                    if (icon.classList.contains('fa-bars')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });
                
                // Close menu when clicking a nav link on mobile
                const navLinks = mainNav.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            mainNav.classList.remove('open');
                            
                            // Reset icon
                            const icon = mobileMenuToggle.querySelector('i');
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                    });
                });
            }
            
            let additionalQuestionCount = 0;
            let currentStep = 1;
            
            // Initial setup to ensure only the first step is visible
            formSteps.forEach(function(step, index) {
                if (index === 0) {
                    step.style.display = 'block';
                } else {
                    step.style.display = 'none';
                }
            });

            // Toggle challenging questions explanation
            challengingRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.value === 'yes' && this.checked) {
                        challengingQuestionsSection.style.display = 'block';
                        document.getElementById('challenging-explanation').setAttribute('required', '');
                    } else {
                        challengingQuestionsSection.style.display = 'none';
                        document.getElementById('challenging-explanation').removeAttribute('required');
                    }
                });
            });

            // Next step navigation
            nextButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const nextStep = parseInt(this.getAttribute('data-next'));
                    goToStep(nextStep);
                });
            });

            // Previous step navigation
            prevButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const prevStep = parseInt(this.getAttribute('data-prev'));
                    goToStep(prevStep);
                });
            });

            // Step dots navigation
            stepDots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    const step = parseInt(this.getAttribute('data-step'));
                    goToStep(step);
                });
            });

            // Function to count words in a string
            function countWords(text) {
                return text.trim().split(/\s+/).filter(word => word.length > 0).length;
            }

            // Function to validate questions (minimum 5 words)
            function validateQuestions(stepElement) {
                const questionTextareas = stepElement.querySelectorAll('textarea[id^="question"]');
                let isValid = true;
                let validationErrors = [];

                questionTextareas.forEach(textarea => {
                    const questionText = textarea.value.trim();
                    const wordCount = countWords(questionText);
                    
                    // Check if it's a required field (question1 is required)
                    const isRequired = textarea.hasAttribute('required');
                    
                    if (isRequired && questionText === '') {
                        isValid = false;
                        textarea.classList.add('error');
                        textarea.style.borderColor = 'red';
                        validationErrors.push('Question 1 is required');
                    } else if (questionText !== '' && wordCount < 5) {
                        isValid = false;
                        textarea.classList.add('error');
                        textarea.style.borderColor = 'red';
                        const questionNumber = textarea.id.replace('question', '').replace('additional-question-', '');
                        validationErrors.push(`Question ${questionNumber} must contain at least 5 words (currently has ${wordCount} words)`);
                    } else {
                        textarea.classList.remove('error');
                        textarea.style.borderColor = '';
                    }
                });

                return { isValid, errors: validationErrors };
            }

            // Function to navigate to a specific step
            function goToStep(stepNumber) {
                // Validate current step fields before proceeding to next
                if (stepNumber > currentStep) {
                    const currentStepElement = document.getElementById(`step-${currentStep}`);
                    const requiredFields = currentStepElement.querySelectorAll('[required]');
                    let isValid = true;
                    let validationErrors = [];
                    
                    // Regular field validation
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('error');
                            field.style.borderColor = 'red';
                        } else {
                            field.classList.remove('error');
                            field.style.borderColor = '';
                        }
                    });
                    
                    // Special validation for step 4 (questions)
                    if (currentStep === 4) {
                        const questionValidation = validateQuestions(currentStepElement);
                        if (!questionValidation.isValid) {
                            isValid = false;
                            validationErrors = questionValidation.errors;
                        }
                    }
                    
                    if (!isValid) {
                        if (validationErrors.length > 0) {
                            alert('Please fix the following issues:\n\n• ' + validationErrors.join('\n• '));
                        } else {
                            alert('Please fill in all required fields before proceeding.');
                        }
                        return;
                    }
                }
                
                // Hide all steps
                formSteps.forEach(function(step) {
                    step.style.display = 'none';
                    step.classList.remove('active');
                });
                
                // Show the target step
                const targetStep = document.getElementById(`step-${stepNumber}`);
                targetStep.style.display = 'block';
                targetStep.classList.add('active');
                
                // Update step dots
                stepDots.forEach(function(dot) {
                    dot.classList.remove('active');
                });
                stepDots[stepNumber - 1].classList.add('active');
                
                // Update current step
                currentStep = stepNumber;
                
                // Scroll to top of form
                window.scrollTo({
                    top: form.offsetTop - 50,
                    behavior: 'smooth'
                });
            }

            // Function to update word count for a textarea
            function updateWordCount(textarea) {
                const wordCount = countWords(textarea.value);
                const wordCountElement = document.getElementById(textarea.id + '-word-count');
                
                if (wordCountElement) {
                    const isRequired = textarea.hasAttribute('required');
                    const isEmpty = textarea.value.trim() === '';
                    
                    if (isEmpty) {
                        wordCountElement.textContent = isRequired ? 'Word count: 0 (minimum 5 words required)' : 'Word count: 0';
                        wordCountElement.style.color = '#6c757d';
                    } else if (wordCount < 5) {
                        wordCountElement.textContent = `Word count: ${wordCount} (minimum 5 words required)`;
                        wordCountElement.style.color = '#e74c3c';
                        textarea.style.borderColor = '#e74c3c';
                    } else {
                        wordCountElement.textContent = `Word count: ${wordCount} ✓`;
                        wordCountElement.style.color = '#28a745';
                        textarea.style.borderColor = '#28a745';
                    }
                }
            }

            // Add real-time word count validation to default questions
            for (let i = 1; i <= 4; i++) {
                const textarea = document.getElementById(`question${i}`);
                if (textarea) {
                    textarea.addEventListener('input', function() {
                        updateWordCount(this);
                    });
                    // Initialize word count
                    updateWordCount(textarea);
                }
            }

            // Function to add a new question
            addQuestionBtn.addEventListener('click', function() {
                additionalQuestionCount++;
                const questionNumber = 4 + additionalQuestionCount;
                
                const newQuestionGroup = document.createElement('div');
                newQuestionGroup.className = 'form-group question-item';
                newQuestionGroup.style.position = 'relative';
                newQuestionGroup.style.border = '1px solid #ddd';
                newQuestionGroup.style.padding = '20px';
                newQuestionGroup.style.borderRadius = '0';
                newQuestionGroup.style.marginBottom = '15px';
                newQuestionGroup.innerHTML = `
                    <div class="question-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label for="additional-question-${additionalQuestionCount}" style="font-weight: 600; color: var(--primary);">Question ${questionNumber}</label>
                    </div>
                    <textarea id="additional-question-${additionalQuestionCount}" class="form-control" placeholder="Enter the interview question (minimum 5 words required)"></textarea>
                    <div class="question-word-count" id="additional-question-${additionalQuestionCount}-word-count" style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                        Word count: 0 (minimum 5 words required)
                    </div>
                    <button type="button" class="remove-question" style="position: absolute; top: 15px; right: 15px; color: #e74c3c; background: none; border: 1px solid #e74c3c; border-radius: 0; padding: 5px 10px; cursor: pointer; font-size: 14px; display: flex; align-items: center;">
                        <i class="fas fa-trash-alt"></i> <span style="margin-left: 5px;">Delete Question</span>
                    </button>
                `;
                
                additionalQuestionsContainer.appendChild(newQuestionGroup);

                // Add real-time word count validation to new question
                const newTextarea = newQuestionGroup.querySelector('textarea');
                newTextarea.addEventListener('input', function() {
                    updateWordCount(this);
                });
                updateWordCount(newTextarea);

                // Add remove functionality
                const removeBtn = newQuestionGroup.querySelector('.remove-question');
                removeBtn.addEventListener('click', function() {
                    additionalQuestionsContainer.removeChild(newQuestionGroup);
                    renumberQuestions();
                });
            });

            // Function to renumber all questions
            function renumberQuestions() {
                const defaultQuestions = document.querySelectorAll('#questions-container .form-group');
                const additionalQuestions = document.querySelectorAll('#additional-questions .form-group');
                
                // Renumber additional questions
                additionalQuestions.forEach((item, index) => {
                    const label = item.querySelector('label');
                    label.textContent = `Question ${defaultQuestions.length + index + 1}`;
                    label.setAttribute('for', `additional-question-${index + 1}`);
                    
                    const textarea = item.querySelector('textarea');
                    textarea.id = `additional-question-${index + 1}`;
                });
            }
            
            // Password modal functionality
            analyticsLink.addEventListener('click', function(e) {
                e.preventDefault();
                openPasswordModal();
            });
            
            function openPasswordModal() {
                passwordModal.style.display = 'block';
                adminPasswordInput.value = '';
                passwordError.style.display = 'none';
                adminPasswordInput.focus();
            }
            
            function closePasswordModal() {
                passwordModal.style.display = 'none';
            }
            
            closeModalBtn.addEventListener('click', closePasswordModal);
            cancelPasswordBtn.addEventListener('click', closePasswordModal);
            
            // Close modal when clicking outside of it
            window.addEventListener('click', function(e) {
                if (e.target === passwordModal) {
                    closePasswordModal();
                }
            });
            
            // Allow pressing Enter to submit password
            adminPasswordInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    submitPasswordBtn.click();
                }
            });
            
            // Submit password
            submitPasswordBtn.addEventListener('click', function() {
                const password = adminPasswordInput.value;
                
                // This would normally be a server-side validation
                // For demonstration, we're using a simple hardcoded password
                if (password === 'admin123') {
                    window.location.href = 'dashboard.php';
                } else {
                    passwordError.style.display = 'block';
                    adminPasswordInput.focus();
                }
            });

            // Form submission - UPDATED TO INCLUDE ALL NEW FIELDS
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Collect all the form data with updated structure including ALL new fields
                const formData = {
                    recruiterInfo: {
                        employeeId: document.getElementById('employee-id').value,
                        name: document.getElementById('recruiter-name').value,
                        email: document.getElementById('recruiter-email').value,
                        businessUnit: document.getElementById('business-unit').value
                    },
                    interviewDetails: {
                        date: document.getElementById('interview-date').value,
                        position: document.getElementById('position').value,
                        systemIntegrator: document.getElementById('system-integrator').value,
                        clientName: document.getElementById('client-name').value,
                        interviewerName: document.getElementById('interviewer-name').value,
                        clientManagerName: document.getElementById('client-manager-name').value,      // NEW
                        clientManagerEmail: document.getElementById('client-manager-email').value,   // NEW
                        geo: document.getElementById('geo').value                                     // NEW
                    },
                    candidateInfo: {                                                                  // NEW SECTION
                        name: document.getElementById('candidate-name').value,
                        email: document.getElementById('candidate-email').value,
                        applicantId: document.getElementById('applicant-id').value
                    },
                    clientQuestions: [],
                    candidateReflections: {
                        askedAvailability: document.querySelector('input[name="availability"]:checked')?.value,
                        answeredConfidently: document.querySelector('input[name="answer-confidently"]:checked')?.value,
                        hadChallengingQuestions: document.querySelector('input[name="challenging"]:checked')?.value,
                        challengingExplanation: document.getElementById('challenging-explanation').value
                    },
                    additionalComments: document.getElementById('additional-comments').value
                };
                
                // Collect default questions
                for (let i = 1; i <= 4; i++) {
                    formData.clientQuestions.push(document.getElementById(`question${i}`).value);
                }
                
                // Collect additional questions
                const additionalQuestionsElements = document.querySelectorAll('#additional-questions textarea');
                additionalQuestionsElements.forEach(element => {
                    formData.clientQuestions.push(element.value);
                });
                
                // Display loading state
                const submitBtn = document.querySelector('.submit-btn');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                submitBtn.disabled = true;
                
                // Send data to server
                fetch('submit_feedback.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success:', data);
                    
                    // Show success message
                    successMessage.style.display = 'block';
                    successMessage.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message || 'Thank you for your feedback! Your insights will help other candidates prepare better.'}`;
                    
                    // Reset form after 3 seconds
                    setTimeout(() => {
                        form.reset();
                        successMessage.style.display = 'none';
                        
                        // Clear additional questions
                        additionalQuestionsContainer.innerHTML = '';
                        additionalQuestionCount = 0;
                        
                        // Reset challenging questions section
                        challengingQuestionsSection.style.display = 'none';
                        
                        // Go back to step 1
                        goToStep(1);
                        
                        // Reset button
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }, 3000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Show error message
                    successMessage.style.display = 'block';
                    successMessage.innerHTML = '<i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> There was an error submitting your feedback. Please try again.';
                    successMessage.style.backgroundColor = 'rgba(231, 76, 60, 0.1)';
                    successMessage.style.color = 'var(--danger)';
                    
                    // Reset button
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                    
                    // Hide error message after 5 seconds
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                        successMessage.style.backgroundColor = '';
                        successMessage.style.color = '';
                    }, 5000);
                });
            });

            // Initialize navigation menu click handlers
            const navLinks = document.querySelectorAll('nav ul li a');
            navLinks.forEach(link => {
                if (link.id !== 'analytics-link') {  // Skip analytics link as it has its own handler
                    link.addEventListener('click', function(e) {
                        // Don't call preventDefault() to allow normal link navigation
                        
                        // Remove active class from all links
                        navLinks.forEach(item => item.classList.remove('active'));
                        
                        // Add active class to clicked link
                        this.classList.add('active');
                    });
                }
            });
        });
    </script>
</body>
</html>