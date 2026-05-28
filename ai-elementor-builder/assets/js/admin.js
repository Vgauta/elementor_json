(function (wp) {
  const { createElement: h, useState } = wp.element;

  function App() {
    const [file, setFile] = useState(null);
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [pageId, setPageId] = useState('');
    const [category, setCategory] = useState('custom');
    const [tags, setTags] = useState('hero,landing');
    const [saveMsg, setSaveMsg] = useState('');

    const api = (path, options = {}) => fetch(AIEB_CONFIG.restUrl + path, {
      ...options,
      headers: { ...(options.headers || {}), 'X-WP-Nonce': AIEB_CONFIG.nonce }
    });

    const generate = async () => {
      if (!file || loading) return;
      setLoading(true); setError('');
      const form = new FormData(); form.append('image', file);
      try {
        const res = await api('/generate', { method: 'POST', body: form });
        const data = await res.json(); if (!res.ok) throw new Error(data?.message || 'Generation failed');
        setResult(data);
      } catch (e) { setError(e.message || 'Unexpected error'); } finally { setLoading(false); }
    };

    const savePreset = async () => {
      setSaveMsg('');
      const payload = {
        page_id: Number(pageId),
        category,
        tags: tags.split(',').map(s => s.trim()).filter(Boolean),
        pattern: { type: 'custom', layout: 'custom', elements: [] }
      };
      const res = await api('/save-preset', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const data = await res.json();
      setSaveMsg(res.ok ? `Saved preset: ${data?.preset?.id}` : (data?.message || 'Save failed'));
    };

    return h('div', { className: 'aieb-app' },
      h('h1', null, 'AI Elementor Builder'),
      h('p', null, 'AI analyzes screenshot patterns, then template registry assembles from valid presets.'),
      h('input', { type: 'file', accept: 'image/png,image/jpeg,image/webp', onChange: (e) => setFile(e.target.files[0] || null) }),
      h('button', { className: 'button button-primary', onClick: generate, disabled: loading || !file }, loading ? 'Generating...' : 'Generate'),
      error && h('div', { className: 'notice notice-error inline' }, h('p', null, error)),
      result && h('div', { className: 'aieb-preview' },
        h('h2', null, 'Validation Report'),
        h('pre', null, JSON.stringify(result.validation, null, 2)),
        h('h2', null, 'Compatibility'),
        h('pre', null, JSON.stringify(result.compatibility, null, 2)),
        h('h2', null, 'Elementor JSON Preview'),
        h('pre', null, JSON.stringify(result.elementor_json, null, 2))
      ),
      h('hr'),
      h('h2', null, 'Template Learning'),
      h('p', null, 'Save an existing Elementor page as reusable preset with category and tags.'),
      h('input', { type: 'number', placeholder: 'Elementor Page ID', value: pageId, onChange: e => setPageId(e.target.value) }),
      h('input', { type: 'text', placeholder: 'Category', value: category, onChange: e => setCategory(e.target.value) }),
      h('input', { type: 'text', placeholder: 'Tags comma-separated', value: tags, onChange: e => setTags(e.target.value) }),
      h('button', { className: 'button', onClick: savePreset, disabled: !pageId }, 'Save as Preset'),
      saveMsg && h('p', null, saveMsg)
    );
  }

  wp.element.render(h(App), document.getElementById('aieb-admin-root'));
})(window.wp);
