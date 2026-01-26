<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-attachment: fixed;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-box {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }

        .error-code {
            font-size: 48px;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 15px;
        }

        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
            text-align: left;
        }

        .button-group {
            display: flex;
            gap: 15px;
            flex-direction: row-reverse;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #bdc3c7;
            transform: translateY(-2px);
        }

        .details {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            font-size: 13px;
            color: #999;
            text-align: right;
        }

        .info-box {
            background: #e8f4f8;
            border-right: 4px solid #3498db;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: right;
            color: #2c3e50;
        }

        .urdu-text {
            direction: rtl;
            unicode-bidi: embed;
        }

        @media (max-width: 600px) {
            .modal-box {
                padding: 30px 20px;
            }

            .error-code {
                font-size: 36px;
            }

            .error-title {
                font-size: 22px;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="error-icon">🔒</div>

            <div class="error-code">403</div>

            <div class="error-title">
                Access Denied
            </div>

            <div class="info-box">
                ⚠️ You do not have permission to access this page
            </div>

            <div class="error-message">
                Sorry! You don't have the required permissions to access this resource at this time. 
                Please contact your administrator for assistance.
            </div>

            <div class="button-group">
                <button class="btn btn-primary" onclick="goHome()">
                    🏠 Home
                </button>
                <button class="btn btn-secondary" onclick="goBack()">
                    ← Back
                </button>
            </div>

            <div class="details">
                <p>If you believe this is an error, please contact your administrator</p>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        function goHome() {
            window.location.href = "{{ url('/') }}";
        }

        // Keyboard shortcut
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                goBack();
            }
        });
    </script>
</body>
</html>
