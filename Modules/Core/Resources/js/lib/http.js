const token = () => (typeof document !== 'undefined' ? document.querySelector('meta[name="csrf-token"]')?.content : null)
const currentLocale = () => (typeof window !== 'undefined' ? localStorage.getItem('ht-locale') : null) || 'en-IN'

export async function request(url, options = {}) {
  const { headers, ...restOptions } = options
  const csrf = token()

  const finalHeaders = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-Locale': currentLocale(),
    ...(headers || {}),
  }

  if (csrf) {
    finalHeaders['X-CSRF-TOKEN'] = csrf
  }

  if (restOptions.body && !(typeof FormData !== 'undefined' && restOptions.body instanceof FormData) && !finalHeaders['Content-Type']) {
    finalHeaders['Content-Type'] = 'application/json'
  }

  const response = await fetch(url, {
    credentials: 'same-origin',
    ...restOptions,
    headers: finalHeaders,
  })

  const contentType = response.headers.get('content-type') || ''
  const isJson = contentType.includes('application/json')

  if (!response.ok) {
    const body = isJson ? await response.json().catch(() => ({})) : {}
    const error = new Error(body.message || Object.values(body.errors || {})[0]?.[0] || response.statusText || 'Something went wrong.')
    error.errors = body.errors || {}
    error.status = response.status
    throw error
  }

  if (response.status === 204) return null
  return isJson ? response.json() : response.text()
}
