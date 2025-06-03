
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

  const queryParams = new URLSearchParams();

  // Add context data as query parameters
  Object.entries(contextData).forEach(([key, value]) => {
    queryParams.append(key, value);
  });

  const response = await fetch(`/super-admin-toolbar?${queryParams.toString()}`, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
    credentials: 'same-origin',
  });

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
