export function errMsg(e: any, fallback = 'Something went wrong. Please try again.') {
  return e?.data?.message || fallback
}

export function useApi() {
  const config = useRuntimeConfig()
  const token = useCookie('token')
  const user = useCookie('user')

  async function request(path: string, options: any = {}) {
    try {
      return await $fetch<any>((config.public.apiBase as string) + path, {
        ...options,
        headers: {
          Accept: 'application/json',
          ...options.headers,
          ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
        },
      })
    } catch (e: any) {
      if (e?.response?.status === 401 && path !== '/login') {
        token.value = null
        user.value = null
        await navigateTo('/login')
      }
      throw e
    }
  }

  return {
    get: (path: string) => request(path),
    post: (path: string, body?: any) => request(path, { method: 'POST', body }),
    put: (path: string, body?: any) => request(path, { method: 'PUT', body }),
    del: (path: string) => request(path, { method: 'DELETE' }),
  }
}
