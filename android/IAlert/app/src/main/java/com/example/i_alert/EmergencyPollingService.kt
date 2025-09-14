package com.example.i_alert

import android.Manifest
import android.annotation.SuppressLint
import android.app.*
import android.content.Context
import android.content.Intent
import android.media.MediaPlayer
import android.os.Build
import android.os.IBinder
import android.util.Log
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import android.app.PendingIntent
import android.content.pm.ServiceInfo
import android.provider.Settings
import androidx.annotation.RequiresPermission
import com.google.gson.Gson
import com.google.gson.JsonObject
import com.google.gson.JsonArray
import kotlinx.coroutines.*
import kotlinx.coroutines.Dispatchers.IO
import kotlinx.coroutines.Dispatchers.Main
import java.net.URL



class EmergencyPollingService : Service() {
    private val TAG = "EmergencyPollingService"
    private var job: Job? = null
    private var mediaPlayer: MediaPlayer? = null
    private var lastPendingCount = 0

    @SuppressLint("MissingPermission")  // 👈 MOVE IT HERE — ABOVE THE METHOD
    override fun onCreate() {
        super.onCreate()
        createNotificationChannel()
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            startForeground(
                1,
                createNotification(),
                ServiceInfo.FOREGROUND_SERVICE_TYPE_DATA_SYNC
            )
        } else {
            startForeground(1, createNotification())
        }
        Log.d(TAG, "Service started in foreground")
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        startPolling()
        return START_STICKY
    }


    private fun startPolling() {
        job = CoroutineScope(IO).launch {
            val prefs = applicationContext.getSharedPreferences("user_prefs", Context.MODE_PRIVATE)

            while (isActive) {
                try {
                    // 👇 Get userType — NO DEFAULT. If null, skip polling.
                    val userType = prefs.getString("userType", null)

                    // ✅ Stop polling if userType is not set or is "civilian" (which your site doesn't use)
                    if (userType.isNullOrEmpty() || userType == "civilian") {
                        Log.d(TAG, "No valid userType — skipping poll")
                        delay(3000)
                        continue
                    }

                    val urlString = "https://i-alert.site/controllers/get_android_emergency.php?userType=$userType"
                    val url = URL(urlString)

                    val response = withContext(IO) { url.readText() }
                    val json = Gson().fromJson(response, JsonObject::class.java)

                    val data = if (json.has("data") && json.get("data").isJsonArray) {
                        json.getAsJsonArray("data")
                    } else {
                        JsonArray()
                    }

                    val currentCount = data.size()

                    withContext(Main) {
                        if (currentCount > 0 && currentCount != lastPendingCount) {
                            Log.d(TAG, "New emergency data for $userType! Triggering alarm...")
                            triggerAlarm()
                        } else if (currentCount == 0 && lastPendingCount > 0) {
                            Log.d(TAG, "All emergencies resolved for $userType!")
                        }
                        lastPendingCount = currentCount
                    }

                } catch (e: Exception) {
                    Log.e(TAG, "Polling error: ${e.message}")
                }

                delay(3000)
            }
        }
    }

    @SuppressLint("MissingPermission")
    private fun triggerAlarm() {
        playAlarmSound()
        showNotification("🚨 New Emergency!", "Immediate attention required!")
    }

    private fun playAlarmSound() {
        try {
            mediaPlayer?.let {
                if (it.isPlaying) it.stop()
                it.release()
            }
            mediaPlayer = MediaPlayer.create(this, R.raw.alarm)
            mediaPlayer?.setVolume(1f, 1f)
            mediaPlayer?.isLooping = false
            mediaPlayer?.start()
        } catch (e: Exception) {
            Log.e(TAG, "Failed to play alarm: ${e.message}")
        }
    }

    @RequiresPermission(Manifest.permission.POST_NOTIFICATIONS)
    private fun showNotification(title: String, content: String) {
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        }
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M)
                PendingIntent.FLAG_IMMUTABLE
            else
                0
        )

        val notification = NotificationCompat.Builder(this, "emergency_channel")
            .setContentTitle(title)
            .setContentText(content)
            .setSmallIcon(R.drawable.ic_launcher_foreground)
            .setContentIntent(pendingIntent)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .setSound(Settings.System.DEFAULT_NOTIFICATION_URI)
            .build()

        NotificationManagerCompat.from(this).notify(1, notification)
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                "emergency_channel",
                "Emergency Alerts",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Emergency alert notifications"
                enableVibration(true)
                enableLights(true)
            }
            val manager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            manager.createNotificationChannel(channel)
        }
    }

    private fun createNotification(): Notification {
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        }
        val pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M)
                PendingIntent.FLAG_IMMUTABLE
            else
                0
        )

        return NotificationCompat.Builder(this, "emergency_channel")
            .setContentTitle("I-Alert Running")
            .setContentText("Monitoring for emergencies...")
            .setSmallIcon(R.drawable.ic_notification)  // ← This must exist
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .build()
    }

    override fun onDestroy() {
        super.onDestroy()
        job?.cancel()
        mediaPlayer?.release()
        Log.d(TAG, "Service destroyed")
    }

    override fun onBind(intent: Intent?): IBinder? = null
}