export function el(tag, attributes = {}, ...children) {
  const node = document.createElement(tag);
  for (const [key, value] of Object.entries(attributes)) {
    if (value === null || value === undefined) continue;
    if (key === 'class') {
      node.className = String(value);
      continue;
    }
    if (key === 'dataset') {
      Object.assign(node.dataset, value);
      continue;
    }
    if (key === 'html') {
      node.innerHTML = String(value);
      continue;
    }
    node.setAttribute(key, value === true ? '' : String(value));
  }
  for (const child of children.flat()) {
    if (child === null || child === undefined) continue;
    if (typeof child === 'string' || typeof child === 'number') {
      node.appendChild(document.createTextNode(String(child)));
    } else if (child instanceof HTMLElement) {
      node.appendChild(child);
    }
  }
  return node;
}
