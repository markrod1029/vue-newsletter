import { registerSW } from 'virtual:pwa-register'

const updateSW = registerSW({
  onNeedRefresh() {
    // Show a prompt to user when new content is available
    const updateDialog = confirm('New content available. Reload to update?')
    if (updateDialog) {
      updateSW()
    }
  },
  onOfflineReady() {
    console.log('App ready to work offline')
    // You can show a notification here
    alert('App is ready to work offline!')
  },
  onRegisteredSW(swScriptUrl, registration) {
    console.log('SW registered: ', swScriptUrl)
    
    // Periodically check for updates (every 1 hour)
    if (registration) {
      setInterval(() => {
        registration.update()
      }, 60 * 60 * 1000) // 1 hour
    }
  }
})

// Export for potential manual control
export { updateSW }