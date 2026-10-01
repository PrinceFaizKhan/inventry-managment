const token = () => document.querySelector('meta[name="csrf-token"]')?.content
export async function request(url, options = {}) {
  const response = await fetch(url, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token(), ...(options.headers || {}) }, credentials: 'same-origin', ...options })
  if (!response.ok) { const body = await response.json().catch(() => ({})); throw new Error(body.message || body.errors?.email?.[0] || 'Something went wrong.') }
  return response.status === 204 ? null : response.json()
}
