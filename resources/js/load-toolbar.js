function getMetaToken() {
  const tag = document.querySelector('meta[name="csrf-token"]');
  if (tag) {
    return tag.getAttribute('content');
  }

  return '';
}

function setMetaToken(token) {
  let tag = document.querySelector('meta[name="csrf-token"]');

  if (!tag) {
    tag = document.createElement('meta');
    tag.setAttribute('name', 'csrf-token');
    document.head.appendChild(tag);
  }

  tag.setAttribute('content', token);
}

function updateLivewireToken(token) {
  if (window.livewireScriptConfig) {
    window.livewireScriptConfig.csrf = token;
  }

  if (window.Livewire) {
    if (typeof window.Livewire.updateCsrfToken === 'function') {
      window.Livewire.updateCsrfToken(token);
    } else if ('csrfToken' in window.Livewire) {
      window.Livewire.csrfToken = token;
    }
  }
}

function getCookie(name) {
  const cookieString = `; ${document.cookie}`;
  const parts = cookieString.split(`; ${name}=`);
  if (parts.length === 2) {
    return parts.pop().split(';').shift();
  }

  return null;
}

function getCsrfTokenFromResponse(response) {
  let token = response.headers.get('x-csrf-token');

  if (!token) {
    token = response.headers.get('X-CSRF-TOKEN');
  }

  if (!token) {
    const cookie = getCookie('XSRF-TOKEN');
    if (cookie !== null) {
      token = decodeURIComponent(cookie);
    }
  }

  return token;
}

function getContextData() {
  const contextTag = document.getElementById('super-admin-toolbar-context-json');

  if (!contextTag) {
    return {};
  }

  const text = contextTag.textContent.trim();

  if (text.length === 0) {
    return {};
  }

  try {
    return JSON.parse(text);
  } catch (error) {
    console.error('Super Admin Toolbar: invalid JSON in context tag.', error);
    return {};
  }
}

function insertToolbarHtml(html) {
  document.body.insertAdjacentHTML('beforeend', html);
}

function createToolbarStyleSheet(href) {
  const link = document.createElement('link');
  link.setAttribute('rel', 'stylesheet');
  link.setAttribute('href', href);
  document.head.appendChild(link);
}

function createToolbarScript(src) {
  const script = document.createElement('script');
  script.setAttribute('type', 'module');
  script.setAttribute('src', src);

  script.onload = function () {
    if (typeof SuperAdminToolbar !== 'undefined') {
      const toolbar = new SuperAdminToolbar();
      toolbar.init();
    } else {
      console.error('SuperAdminToolbar class not found after loading script.');
    }
  };

  script.onerror = function () {
    console.error('Failed to load toolbar script:', src);
  };

  document.body.appendChild(script);
}

async function loadToolbar() {
  const toolbarExists = document.getElementById('super-admin-toolbar');

  if (toolbarExists) {
    return;
  }

  const contextData = getContextData();

  const response = await fetch('/super-admin-toolbar', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': getMetaToken(),
    },
    credentials: 'same-origin',
    body: JSON.stringify(contextData),
  });

  const rotatedToken = getCsrfTokenFromResponse(response);

  if (rotatedToken) {
    setMetaToken(rotatedToken);
    updateLivewireToken(rotatedToken);
  }

  if (!response.ok) {
    throw new Error(`HTTP error. Status: ${response.status}`);
  }

  const data = await response.json();

  if (!data || data.authenticated === false) {
    return;
  }

  if (data.html) {
    insertToolbarHtml(data.html);
  }

  if (data.css) {
    createToolbarStyleSheet(data.css);
  }

  if (data.js) {
    createToolbarScript(data.js);
  }
}

document.addEventListener('DOMContentLoaded', function () {
  loadToolbar().catch(function (error) {
    if (error === 'unauthorized') {
      return;
    }

    console.error('Error loading Super Admin Toolbar:', error);
  });
});
