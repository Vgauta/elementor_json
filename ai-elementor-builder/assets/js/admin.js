(function (wp) {
  const { createElement: h, useState } = wp.element;

  function App() {
    const [file, setFile] = useState(null);
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const generate = async () => {
      if (!file || loading) return;
      setLoading(true);
      setError('');
      const form = new FormData();
      form.append('image', file);

      try {
        const res = await fetch(AIEB_CONFIG.restUrl + '/generate', {
          method: 'POST',
          headers: { 'X-WP-Nonce': AIEB_CONFIG.nonce },
          body: form,
        });
        const data = await res.json();
        if (!res.ok) {
          throw new Error(data?.message || 'Generation failed');
        }
        setResult(data);
      } catch (e) {
        setError(e.message || 'Unexpected error');
      } finally {
        setLoading(false);
      }
    };

    const download = () => {
      if (!result?.elementor_json) return;
      const blob = new Blob([JSON.stringify(result.elementor_json, null, 2)], { type: 'application/json' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = result.download_name || 'template.json';
      a.click();
      URL.revokeObjectURL(a.href);
    };

    return h('div', { className: 'aieb-app' },
      h('h1', null, 'AI Elementor Builder'),
      h('p', null, 'Container-only Elementor JSON generation from screenshot semantics.'),
      h('input', { type: 'file', accept: 'image/png,image/jpeg,image/webp', onChange: (e) => setFile(e.target.files[0] || null) }),
      h('button', { className: 'button button-primary', onClick: generate, disabled: loading || !file }, loading ? 'Generating...' : 'Generate'),
      error && h('div', { className: 'notice notice-error inline' }, h('p', null, error)),
      result && h('div', { className: 'aieb-preview' },
        h('h2', null, 'Preview Layout Structure'),
        h('pre', null, JSON.stringify(result.analysis, null, 2)),
        h('h2', null, 'Elementor JSON Preview'),
        h('pre', null, JSON.stringify(result.elementor_json, null, 2)),
        h('button', { className: 'button', onClick: download }, 'Download Elementor JSON'),
        h('a', { className: 'button button-secondary', href: 'admin.php?page=elementor-tools#tab-import-export-kit', style: { marginLeft: '8px' } }, 'Import in Elementor')
      )
    );
  }

  wp.element.render(h(App), document.getElementById('aieb-admin-root'));
})(window.wp);
