const name = (label: string, maxlength = 100) => ({
  key: 'name', label, type: 'text', required: true, isName: true, maxlength,
})

export const masters = {
  categories: {
    resource: 'categories',
    label: 'Category',
    hint: 'Only active categories appear in the Item form.',
    hasActive: true,
    fields: [name('Category Name')],
    columns: [{ key: 'id', label: 'ID' }, { key: 'name', label: 'Category Name' }],
  },
  brands: {
    resource: 'brands',
    label: 'Brand',
    hint: 'Letters, numbers and underscore only.',
    hasActive: false,
    fields: [name('Brand Name')],
    columns: [{ key: 'id', label: 'ID' }, { key: 'name', label: 'Brand Name' }],
  },
  uoms: {
    resource: 'uoms',
    label: 'UOM',
    hint: 'Letters, numbers and underscore only.',
    hasActive: false,
    fields: [name('UOM Name')],
    columns: [{ key: 'id', label: 'ID' }, { key: 'name', label: 'UOM Name' }],
  },
  locations: {
    resource: 'locations',
    label: 'Location',
    hint: 'Only active locations appear in Purchase Order Entry.',
    hasActive: true,
    fields: [
      name('Location Name'),
      { key: 'address', label: 'Address', type: 'textarea', required: true },
    ],
    columns: [{ key: 'name', label: 'Name' }, { key: 'address', label: 'Address' }],
  },
  suppliers: {
    resource: 'suppliers',
    label: 'Supplier',
    hint: 'Active suppliers appear in the Supplier dropdown of Purchase Order Entry.',
    hasActive: true,
    fields: [
      name('Supplier Name', 150),
      { key: 'email', label: 'Email Address', type: 'email', required: true, maxlength: 100 },
      { key: 'phone', label: 'Phone Number', type: 'text', required: true, maxlength: 30 },
      { key: 'address', label: 'Address', type: 'textarea', required: true },
    ],
    columns: [
      { key: 'name', label: 'Name' },
      { key: 'phone', label: 'Phone' },
      { key: 'email', label: 'Email' },
    ],
  },
}