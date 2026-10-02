const token = () => document.querySelector('meta[name="csrf-token"]')?.content
export async function request(url, options = {}) {
  const response = await fetch(url, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token(), ...(options.headers || {}) }, credentials: 'same-origin', ...options })
  if (!response.ok) { const body = await response.json().catch(() => ({})); const error = new Error(body.message || Object.values(body.errors || {})[0]?.[0] || 'Something went wrong.'); error.errors = body.errors || {}; throw error }
  return response.status === 204 ? null : response.json()
}
