const fs = require('fs');
let content = fs.readFileSync('c:/laragon/www/kiron-frontend/src/pages/groups/customers/CustomerGroups.jsx', 'utf8');

// fetch groups
content = content.replace(
  '          status:\r\n            selectedStatus === "All"\r\n              ? null\r\n              : selectedStatus === "Active"\r\n                ? Status.Active\r\n                : Status.Inactive,',
  '          status:\r\n            selectedStatus === "All"\r\n              ? null\r\n              : selectedStatus === "Active"\r\n                ? Status.Active\r\n                : selectedStatus === "Trash"\r\n                ? Status.Trashed\r\n                : Status.Inactive,'
);

if (!content.includes('Status.Trashed')) {
  content = content.replace(
    '          status:\n            selectedStatus === "All"\n              ? null\n              : selectedStatus === "Active"\n                ? Status.Active\n                : Status.Inactive,',
    '          status:\n            selectedStatus === "All"\n              ? null\n              : selectedStatus === "Active"\n                ? Status.Active\n                : selectedStatus === "Trash"\n                ? Status.Trashed\n                : Status.Inactive,'
  );
}

// sync with items
content = content.replace(
  '      setGroups(res.data.data.data);\r\n      setPagination(res.data.data);\r\n      setLoading(false);',
  '      setGroups(res.data.data.data);\r\n      setPagination(res.data.data);\r\n      syncWithItems(res.data.data.data.map((g) => g.id));\r\n      setLoading(false);'
);

if (!content.includes('syncWithItems(')) {
  content = content.replace(
    '      setGroups(res.data.data.data);\n      setPagination(res.data.data);\n      setLoading(false);',
    '      setGroups(res.data.data.data);\n      setPagination(res.data.data);\n      syncWithItems(res.data.data.data.map((g) => g.id));\n      setLoading(false);'
  );
}

// header
const oldHeader = `        <div className="flex justify-end md:justify-start gap-2">\r\n          {" "}\r\n          <button\r\n            onClick={() => fetchGroups(1)}\r\n            className="px-2 py-2 border border-gray-300 rounded bg-white flex items-center gap-1"\r\n          >\r\n            <RotateCcw className="w-4 h-4" /> Refresh\r\n          </button>\r\n          <CreateButton\r\n            to="/group/customers/create"\r\n            permission="customer_groups.create"\r\n          />\r\n        </div>`;
const oldHeaderN = oldHeader.replace(/\r/g, '');

const newHeader = `        <BulkActionBar
          resource="customer-groups"
          selectedIds={selectedGroups}
          onRefresh={() => fetchGroups(1)}
          onSuccess={() => {
            clear();
            fetchGroups(1);
          }}
          options={selectedStatus === "Trash" ? TRASH_OPTIONS : ""}
          permission="customer_groups.edit"
          deletePermission="customer_groups.delete"
        >
          <CreateButton
            to="/group/customers/create"
            permission="customer_groups.create"
          />
        </BulkActionBar>`;
        
content = content.replace(oldHeader, newHeader).replace(oldHeaderN, newHeader);

// restore/force delete handlers
const newHandlers = `  const handleRestore = (id) => {
    confirmAction("Are you sure to restore this group?", "restore", async () => {
      try {
        await api.patch(\`/v1/customer-groups/\${id}/restore\`);
        fetchGroups();
      } catch (err) {
        alert("Restore failed");
      }
    });
  };

  const handleForceDelete = (id) => {
    confirmAction("Are you sure to permanently delete this group?", "delete", async () => {
      try {
        await api.delete(\`/v1/customer-groups/\${id}/force\`);
        fetchGroups();
      } catch (err) {
        alert("Permanent delete failed");
      }
    });
  };

  const handleSendEmailToGroup`;

content = content.replace('  const handleSendEmailToGroup', newHandlers);

// table headers
content = content.replace(
  /<th className="px-2 md:px-4 py-3 text-left md:text-center">\s*SL\s*<\/th>/,
  `<th className="px-2 md:px-4 py-2 text-left md:text-center w-12">
                  <input
                    type="checkbox"
                    checked={
                      groups.length > 0 &&
                      groups.every((g) => isSelected(g.id))
                    }
                    onChange={() => toggleAll(groups.map((g) => g.id))}
                  />
                </th>`
);

// table row
content = content.replace(
  /<td className="px-4 py-3 text-center">\s*\{\(pagination\.current_page - 1\) \* pagination\.per_page \+\s*index \+\s*1\}\s*<\/td>/g,
  `<td className="px-2 md:px-4 py-2 text-center">
                      <input
                        type="checkbox"
                        checked={isSelected(group.id)}
                        onChange={() => toggle(group.id)}
                      />
                    </td>`
);

// actions block
content = content.replace(
  /<div className="flex gap-2 justify-center">\s*<div className="flex gap-2 justify-center">\s*<button/g,
  `<TableActions
                        viewTo={\`/group/customers/\${group.id}\`}
                        editTo={\`/group/customers/\${group.id}/edit\`}
                        selectedStatus={selectedStatus}
                        onDelete={() => handleDelete(group.id)}
                        onRestore={() => handleRestore(group.id)}
                        onForceDelete={() => handleForceDelete(group.id)}
                        permissions={{
                          view: "customer_groups.view",
                          edit: "customer_groups.edit",
                          delete: "customer_groups.delete",
                        }}
                      >
                        <button`
);

content = content.replace(
  /<Link[\s\S]*?Eye[\s\S]*?<\/Link>\s*<Link[\s\S]*?svg[\s\S]*?<\/Link>\s*<span[\s\S]*?Trash2[\s\S]*?<\/span>\s*<\/div>\s*<\/div>/g,
  `</TableActions>`
);

content = content.replace('<div className="flex flex-col md:flex-row md:items-center ">', '<div className="flex flex-col-2 md:flex-row md:items-center gap-2 ">');

fs.writeFileSync('c:/laragon/www/kiron-frontend/src/pages/groups/customers/CustomerGroups.jsx', content);
console.log('Update complete');
