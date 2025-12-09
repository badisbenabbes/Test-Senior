import { useEffect, useMemo, useRef, useState } from 'react'
import { searchGitHubUsers } from './api'
import type { GitHubUser } from './types'

type LocalItem = { key: string; user: GitHubUser }

export function App() {
  const [query, setQuery] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [fetched, setFetched] = useState<GitHubUser[]>([])
  const [items, setItems] = useState<LocalItem[]>([])
  const [selected, setSelected] = useState<Set<string>>(new Set())
  const [editMode, setEditMode] = useState<boolean>(false)

  const controllerRef = useRef<AbortController | null>(null)
  const debounceRef = useRef<number | null>(null)

  useEffect(() => {
    const mapped: LocalItem[] = fetched.map(u => ({ key: `gh:${u.id}`, user: u }))
    setItems(mapped)
    setSelected(new Set())
  }, [fetched, query])

  useEffect(() => {
    const trimmed = query.trim();

    if (!trimmed) {
      setFetched([]);
      setError(null);
      setLoading(false);

      return;
    }
    setLoading(true);
    setError(null);

    const timeoutId = window.setTimeout(async () => {
      try {
        const res = await searchGitHubUsers(trimmed);
          setFetched(res.items);
        } catch (e) {
          setFetched([]);
          setError(e instanceof Error ? e.message : 'An error occurred');
        } finally {
          setLoading(false);
        }
    }, 350);

    return () => {
      window.clearTimeout(timeoutId);
    };
  }, [query]);

  const allSelected = useMemo(() => items.length > 0 && selected.size === items.length, [items, selected])
  const someSelected = useMemo(() => selected.size > 0 && selected.size < items.length, [items.length, selected.size])

  function toggleSelectAll() {
    setSelected(allSelected ? new Set() : new Set(items.map(i => i.key)));
  }

  function toggleSelect(key: string) {
    setSelected(prev => {
      const next = new Set(prev);
      next.has(key) ? next.delete(key) : next.add(key);

      return next;
    });
  }


  function duplicateSelected() {
   if (!selected.size) return;
    const now = Date.now();

    const clones: LocalItem[] = items
      .filter(({ key }) => selected.has(key))
      .map((item, index) => ({
         key: `dup:${now}:${index}:${item.user.id}`,
           user: { ...item.user },
         }));

    setItems([...items, ...clones]);
    setSelected(new Set());
  }

  function deleteSelected() {
    if (!selected.size) return

    setItems(prev => prev.filter(i => !selected.has(i.key)))
    setSelected(new Set())
  }

  return (
    <div className="container">
      <header className="header">
        <h1>GitHub user search</h1>
      </header>
        <div className="controls">
          <input
            className="search"
            type="search"
            placeholder="Search GitHub users..."
            value={query}
            onChange={e => setQuery(e.target.value)}
            autoFocus
          />
          <label className="edit-toggle">
            <input type="checkbox" checked={editMode} onChange={e => setEditMode(e.target.checked)} />
            Edit mode
          </label>
        </div>

      {editMode && (
        <div className="toolbar">
          <label className={`select-all ${someSelected ? 'indeterminate' : ''}`}>
            <input
              type="checkbox"
              checked={allSelected}
              onChange={toggleSelectAll}
              aria-checked={someSelected ? 'mixed' : allSelected}
            />
            <span>Select all</span>
          </label>
          <div className="count">{selected.size} selected</div>
          <button className="btn" disabled={selected.size === 0} onClick={duplicateSelected}>Duplicate</button>
          <button className="btn danger" disabled={selected.size === 0} onClick={deleteSelected}>Delete</button>
        </div>
      )}

      {loading && <div className="status">Loading…</div>}
      {!loading && !error && query.trim() && items.length === 0 && (
        <div className="status">No results</div>
      )}

      <main className="grid">
        {items.map(item => (
          <article key={item.key} className="card">
            {editMode && (
              <input
                className="card-checkbox"
                type="checkbox"
                checked={selected.has(item.key)}
                onChange={() => toggleSelect(item.key)}
                aria-label={`Select ${item.user.login}`}
              />
            )}
            <img className="avatar" src={item.user.avatar_url} alt={item.user.login} />
            <div className="info">
              <div className="id">#{item.user.id}</div>
                <div className="id">{item.user.login}</div>
              <a href={item.user.html_url} target="_blank" rel="noreferrer" className="btn">View profile</a>
            </div>
          </article>
        ))}
      </main>
    </div>
  )
}
