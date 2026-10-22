export const modules = [
  {
    key: 'stores',
    title: 'Stores',
    icon: '🏬',
    path: '/stores',
    description: 'Items, categories, brands, UOM and locations',
    groups: [
      {
        title: 'Maintenance',
        links: [
          { to: '/stores/items', icon: '📦', label: 'Items' },
          { to: '/stores/categories', icon: '🗂️', label: 'Item Categories' },
          { to: '/stores/brands', icon: '🏷️', label: 'Brands' },
          { to: '/stores/uoms', icon: '📏', label: 'UOM' },
          { to: '/stores/locations', icon: '📍', label: 'Locations' },
        ],
      },
      { title: 'Transactions', links: [] },
    ],
  },
  {
    key: 'purchases',
    title: 'Purchases',
    icon: '🛒',
    path: '/purchases',
    description: 'Purchase orders and suppliers',
    groups: [
      {
        title: 'Transactions',
        links: [
          { to: '/purchases/po-entry', icon: '📝', label: 'Purchase Order Entry' },
          { to: '/purchases/orders', icon: '📋', label: 'Purchase Orders' },
        ],
      },
      {
        title: 'Maintenance',
        links: [{ to: '/purchases/suppliers', icon: '🚚', label: 'Suppliers' }],
      },
    ],
  },
]