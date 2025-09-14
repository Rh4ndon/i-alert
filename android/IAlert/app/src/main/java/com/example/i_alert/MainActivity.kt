package com.example.i_alert

import android.Manifest
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.net.http.SslError
import android.os.Build
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.util.Log
import android.webkit.GeolocationPermissions
import android.webkit.JavascriptInterface
import android.webkit.PermissionRequest
import android.webkit.SslErrorHandler
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.OnBackPressedCallback
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat

class MainActivity : AppCompatActivity() {
    private lateinit var webView: WebView
    private val TAG = "MainActivity"
    private var isServiceStarted = false

    // Store the geolocation callback for later use
    private var geolocationCallback: GeolocationPermissions.Callback? = null
    private var geolocationOrigin: String? = null
    private var pendingPermissionRequest: PermissionRequest? = null

    private var filePathCallback: ValueCallback<Array<Uri>>? = null
    private val FILECHOOSER_RESULTCODE = 1

    private fun requestIgnoreBatteryOptimizations() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            val intent = Intent(android.provider.Settings.ACTION_REQUEST_IGNORE_BATTERY_OPTIMIZATIONS)
            intent.data = Uri.parse("package:$packageName")
            startActivity(intent)
        }
    }


    // Modern permission launcher
    private val locationPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        val fineLocationGranted = permissions[android.Manifest.permission.ACCESS_FINE_LOCATION] ?: false
        val coarseLocationGranted = permissions[android.Manifest.permission.ACCESS_COARSE_LOCATION] ?: false

        Log.d(TAG, "Location permissions - Fine: $fineLocationGranted, Coarse: $coarseLocationGranted")

        // Handle the geolocation callback if it exists
        geolocationCallback?.let { callback ->
            if (fineLocationGranted || coarseLocationGranted) {
                callback.invoke(geolocationOrigin, true, false)
                Log.d(TAG, "Geolocation permission granted to website")
            } else {
                callback.invoke(geolocationOrigin, false, false)
                Log.d(TAG, "Geolocation permission denied to website")
            }
            // Clear the stored callback
            geolocationCallback = null
            geolocationOrigin = null
        }

        // Handle pending permission request
        pendingPermissionRequest?.let { request ->
            if (fineLocationGranted || coarseLocationGranted) {
                request.grant(request.resources)
                Log.d(TAG, "PermissionRequest granted after Android permission approval")
            } else {
                request.deny()
                Log.d(TAG, "PermissionRequest denied after Android permission rejection")
            }
            pendingPermissionRequest = null
        }

        // Reload the page to let the website detect the new permission state
        if (fineLocationGranted || coarseLocationGranted) {
            webView.reload()
        }
    }

    private val fileChooserLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (filePathCallback == null) return@registerForActivityResult

        var resultUri: Uri? = null
        if (result.resultCode == RESULT_OK && result.data != null) {
            resultUri = result.data?.data
        }

        filePathCallback?.onReceiveValue(if (resultUri != null) arrayOf(resultUri) else null)
        filePathCallback = null
    }

    private fun createImageIntent(): Intent {
        val intent = Intent(Intent.ACTION_GET_CONTENT)
        intent.addCategory(Intent.CATEGORY_OPENABLE)
        intent.type = "image/*"
        return Intent.createChooser(intent, "Select Image")
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_main)

        Log.d(TAG, "Activity created")

        class JsObject {
            @JavascriptInterface
            override fun toString(): String {
                return "injectedObject"
            }
        }

        // Request notification permission for Android 13+
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this, arrayOf(Manifest.permission.POST_NOTIFICATIONS), 102)
            }
        }

        // Start foreground service for background polling
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(this, arrayOf(Manifest.permission.POST_NOTIFICATIONS), 102)
        }



        webView = findViewById(R.id.webview)
        Log.d(TAG, "WebView found: ${webView != null}")

        // Enable debugging (only works on debug builds)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.KITKAT) {
            WebView.setWebContentsDebuggingEnabled(true)
        }

        // Configure WebView settings
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            setGeolocationEnabled(true)
            allowContentAccess = true
            allowFileAccess = true
            setSupportZoom(true)
            builtInZoomControls = true
            displayZoomControls = false
            setPluginState(WebSettings.PluginState.ON)
            setMediaPlaybackRequiresUserGesture(false)
            setJavaScriptCanOpenWindowsAutomatically(true)

            // Additional settings for better location support
            databaseEnabled = true
            setGeolocationDatabasePath(filesDir.path)

            // Enable mixed content mode for HTTPS sites that might load HTTP resources
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
            }
        }
        // Allow insecure content
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            webView.settings.mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
        }


        // Add JavaScript interface
        webView.addJavascriptInterface(JsObject(), "injectedObject")

        // Set up WebViewClient
        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                Log.d(TAG, "Page finished loading: $url")

                // Inject JavaScript to help with location detection
                injectLocationHelperScript()
            }

            override fun onReceivedSslError(
                view: WebView?,
                handler: SslErrorHandler?,
                error: SslError?
            ) {
                // Bypass SSL errors for development
                handler?.proceed()
                Log.w(TAG, "SSL error bypassed: ${error?.toString()}")
            }

            override fun onReceivedError(
                view: WebView?,
                errorCode: Int,
                description: String?,
                failingUrl: String?
            ) {
                super.onReceivedError(view, errorCode, description, failingUrl)
                Log.e(TAG, "WebView error: $errorCode - $description - $failingUrl")
            }

            override fun onReceivedError(
                view: WebView?,
                request: android.webkit.WebResourceRequest?,
                error: android.webkit.WebResourceError?
            ) {
                super.onReceivedError(view, request, error)
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                    Log.e(TAG, "WebResourceError: ${error?.description} - ${request?.url}")
                }
            }

            // Allow all URLs to load
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                return false
            }

            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: android.webkit.WebResourceRequest?
            ): Boolean {
                return false
            }
        }
        requestCameraPermissionIfNeeded()

        // Enhanced WebChromeClient for location handling
        webView.webChromeClient = object : WebChromeClient() {

            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                Log.d(TAG, "onShowFileChooser called")

                // If callback exists, cancel it first
                this@MainActivity.filePathCallback?.onReceiveValue(null)
                this@MainActivity.filePathCallback = filePathCallback

                // Launch file picker
                val intent = createImageIntent()
                fileChooserLauncher.launch(intent)

                return true // Important: return true to indicate you handled it
            }


            override fun onConsoleMessage(consoleMessage: android.webkit.ConsoleMessage?): Boolean {
                consoleMessage?.let {
                    Log.d(TAG, "Console: ${it.message()} at ${it.sourceId()}:${it.lineNumber()}")
                }
                return true
            }

            override fun onPermissionRequest(request: PermissionRequest) {
                Log.d(TAG, "Permission request: ${request.resources.joinToString()}")

                // Check if this is a location permission request
                val isLocationRequest = request.resources.any { resource ->
                    resource == "geolocation" || resource.contains("location", ignoreCase = true)
                }

                if (isLocationRequest) {
                    Log.d(TAG, "Location permission requested via PermissionRequest")
                    if (hasLocationPermissions()) {
                        request.grant(request.resources)
                        Log.d(TAG, "Location permission granted via PermissionRequest")
                    } else {
                        // Store the request and ask for Android permissions
                        pendingPermissionRequest = request
                        requestLocationPermissions()
                        Log.d(TAG, "Requesting Android location permissions first")
                    }
                } else {
                    // For other permission types (camera, microphone, etc.), grant them
                    request.grant(request.resources)
                    Log.d(TAG, "Other permissions granted: ${request.resources.joinToString()}")
                }
            }

            override fun onGeolocationPermissionsShowPrompt(
                origin: String?,
                callback: GeolocationPermissions.Callback?
            ) {
                Log.d(TAG, "Geolocation permission prompt for: $origin")

                if (hasLocationPermissions()) {
                    // Already have Android permissions, grant to website
                    callback?.invoke(origin, true, false)
                    Log.d(TAG, "Location already permitted, granting to website")
                } else {
                    // Store callback for later use and request Android permissions
                    geolocationCallback = callback
                    geolocationOrigin = origin
                    requestLocationPermissions()
                    Log.d(TAG, "Requesting Android location permissions first")
                }
            }

            // Handle JavaScript alerts, confirms, etc. to prevent them from blocking execution
            override fun onJsAlert(
                view: WebView?,
                url: String?,
                message: String?,
                result: android.webkit.JsResult?
            ): Boolean {
                Log.d(TAG, "JS Alert: $message")
                result?.confirm()
                return true
            }

            override fun onJsConfirm(
                view: WebView?,
                url: String?,
                message: String?,
                result: android.webkit.JsResult?
            ): Boolean {
                Log.d(TAG, "JS Confirm: $message")
                result?.confirm()
                return true
            }
        }

        webView.addJavascriptInterface(object {
            @JavascriptInterface
            fun setUserType(type: String) {
                Log.d(TAG, "UserType received from JS: $type")
                getSharedPreferences("user_prefs", Context.MODE_PRIVATE)
                    .edit()
                    .putString("userType", type)
                    .apply()
            }

            @JavascriptInterface
            fun clearUserType() {
                Log.d(TAG, "Clearing userType from SharedPreferences")
                getSharedPreferences("user_prefs", Context.MODE_PRIVATE)
                    .edit()
                    .remove("userType")  // or .putString("userType", "") if you prefer
                    .apply()
            }
        }, "AndroidBridge")

        // Load the URL
        Log.d(TAG, "Loading URL: https://i-alert.site/")
        webView.loadUrl("https://i-alert.site/")

        // Setup back pressed handler
        setupBackPressedHandler()

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }
    }

    private fun injectLocationHelperScript() {
        // Inject JavaScript to help with location detection and permission handling
        val jsCode = """
            // Override navigator.permissions.query for geolocation
            if (navigator.permissions && navigator.permissions.query) {
                const originalPermissionsQuery = navigator.permissions.query;
                navigator.permissions.query = function(permissionDesc) {
                    if (permissionDesc && permissionDesc.name === 'geolocation') {
                        return Promise.resolve({
                            state: 'granted',
                            addEventListener: function() {},
                            removeEventListener: function() {}
                        });
                    }
                    return originalPermissionsQuery.call(navigator, permissionDesc);
                };
            }
            
            // Ensure navigator.geolocation is available
            if (!navigator.geolocation) {
                console.log('Geolocation API not available');
            } else {
                console.log('Geolocation API is available');
            }
            
            console.log('Location helper script injected');
        """.trimIndent()

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.KITKAT) {
            webView.evaluateJavascript(jsCode, null)
        }
    }

    private fun setupBackPressedHandler() {
        val callback = object : OnBackPressedCallback(true) {
            override fun handleOnBackPressed() {
                if (webView.canGoBack()) {
                    webView.goBack()
                } else {
                    isEnabled = false
                    onBackPressedDispatcher.onBackPressed()
                }
            }
        }
        onBackPressedDispatcher.addCallback(this, callback)
    }



    private fun requestLocationPermissions() {
        when {
            hasLocationPermissions() -> {
                Log.d(TAG, "Location permissions already granted")
                // If we have a pending geolocation callback, handle it
                geolocationCallback?.let { callback ->
                    callback.invoke(geolocationOrigin, true, false)
                    geolocationCallback = null
                    geolocationOrigin = null
                }
                // Handle pending permission request
                pendingPermissionRequest?.let { request ->
                    request.grant(request.resources)
                    pendingPermissionRequest = null
                }
                // Reload to let website detect the permission
                webView.reload()
            }
            else -> {
                Log.d(TAG, "Requesting location permissions")
                locationPermissionLauncher.launch(
                    arrayOf(
                        android.Manifest.permission.ACCESS_FINE_LOCATION,
                        android.Manifest.permission.ACCESS_COARSE_LOCATION
                    )
                )
            }
        }
    }

    private fun requestCameraPermissionIfNeeded() {
        if (ContextCompat.checkSelfPermission(this, android.Manifest.permission.CAMERA)
            != PackageManager.PERMISSION_GRANTED
        ) {
            ActivityCompat.requestPermissions(
                this,
                arrayOf(android.Manifest.permission.CAMERA),
                1001
            )
        }
    }

    private fun hasLocationPermissions(): Boolean {
        return ContextCompat.checkSelfPermission(
            this,
            android.Manifest.permission.ACCESS_FINE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED ||
                ContextCompat.checkSelfPermission(
                    this,
                    android.Manifest.permission.ACCESS_COARSE_LOCATION
                ) == PackageManager.PERMISSION_GRANTED
    }

    override fun onResume() {
        super.onResume()
        Log.d(TAG, "Activity resumed")

        if (!isServiceStarted) {
            Handler(Looper.getMainLooper()).postDelayed({
                val serviceIntent = Intent(this, EmergencyPollingService::class.java)
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    startForegroundService(serviceIntent)
                } else {
                    startService(serviceIntent)
                }
                isServiceStarted = true
            }, 500) // 500ms delay
        }

        webView.onResume()
    }


    override fun onPause() {
        super.onPause()
        Log.d(TAG, "Activity paused")
        webView.onPause()
    }

    override fun onDestroy() {
        super.onDestroy()
        webView.destroy()
    }
}