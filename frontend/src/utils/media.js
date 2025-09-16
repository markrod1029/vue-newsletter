// src/utils/media.js
export function getMediaUrl(mediaPath) {
  if (!mediaPath) return ''

  // If already a full URL or base64
  if (
    mediaPath.startsWith('http://') ||
    mediaPath.startsWith('https://') ||
    mediaPath.startsWith('data:')
  ) {
    return mediaPath
  }

  // If it's in Laravel's storage path
  if (mediaPath.startsWith('storage/') || mediaPath.startsWith('/storage/')) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
    const cleanPath = mediaPath.replace(/^\//, '')
    return `${baseUrl}/${cleanPath}`
  }

  // Default: prepend /storage
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://192.168.1.8:8000'
  return `${baseUrl}/storage/${mediaPath}`
}
