(function (wp) {
  const { createElement: h, useState } = wp.element;

  function App() {
    const [file, setFile] = useState(null);
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(false);

    const generate = async () => {
      if (!file) return;
      setLoading(true);
      const form = new FormData();
      form.append('image', file);

      const res = await fetch(AIEB_CONFIG.restUrl + '/generate', {
        method: 'POST',
        headers: { 'X-WP-Nonce': AIEB_CONFIG.nonce },
        body: form,
      });
      setResult(await res.json());
      setLoading(false);
    };

    const download = () => {
      if (!result?.elementor_json) return;
      const blob = new Blob([JSON.stringify(result.elementor_json, null, 2)], { type: 'application/json' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = result.download_name || 'template.json';
      a.click();
    };

    return h('div', { className: 'aieb-app' },
      h('h1', null, 'AI Elementor Builder'),
      h('input', { type: 'file', accept: 'image/*', onChange: (e) => setFile(e.target.files[0]) }),
      h('button', { className: 'button button-primary', onClick: generate, disabled: loading }, loading ? 'Generating...' : 'Generate'),
      result && h('div', { className: 'aieb-preview' },
        h('h2', null, 'Preview Layout Structure'),
        h('pre', null, JSON.stringify(result.analysis, null, 2)),
        h('button', { className: 'button', onClick: download }, 'Download Elementor JSON')
      )
    );
  }

  wp.element.render(h(App), document.getElementById('aieb-admin-root'));
})(window.wp);
