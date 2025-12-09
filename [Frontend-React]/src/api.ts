export async function searchGitHubUsers(q: string): Promise<{ items: any[]; total: number }> {
  if (!q.trim()) return { items: [], total: 0 };

  const res = await fetch(`https://api.github.com/search/users?q=${encodeURIComponent(q)}&per_page=30`);

  if (!res.ok) {
    throw new Error(`GitHub API error: ${res.status}`);
  }

  const data = await res.json();

  return { items: data.items ?? [], total: data.total_count ?? 0 };
}
