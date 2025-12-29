<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Expense Tracker</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--background-color);
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: var(--spacing-xl);
            background: var(--surface-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--elevation-2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .login-header .material-icons {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
        }
        
        .login-header h1 {
            margin: 0;
            font-size: 1.75rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .error-message {
            background: #ffebee;
            border-left: 4px solid var(--error-color);
            color: #c62828;
            padding: var(--spacing-md);
            border-radius: var(--radius-sm);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-sm);
        }
        
        .error-message .material-icons {
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-sm);
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .form-group input {
            width: 100%;
            padding: var(--spacing-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-family: var(--font-family);
            transition: border-color var(--transition-fast);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .btn-login {
            width: 100%;
            padding: var(--spacing-md);
            background: var(--primary-color);
            color: var(--primary-contrast);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
        }
        
        .btn-login:hover {
            background: var(--primary-dark);
        }
        
        .btn-login:active {
            background: var(--primary-dark);
            transform: translateY(1px);
        }
    </style>
</head>
<body>
    <main class="login-container">
        <div class="login-header">
            <span class="material-icons">account_balance_wallet</span>
            <h1>Expense Tracker</h1>
        </div>

        @if($errors->any())
            <div class="error-message">
                <span class="material-icons">error</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}"
                    required 
                    autofocus
                    autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">
                <span class="material-icons">login</span>
                <span>Login</span>
            </button>
        </form>
    </main>
</body>
</html>
