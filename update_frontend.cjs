const fs = require('fs');

function updateFile(filePath) {
  let code = fs.readFileSync(filePath, 'utf8');

  if (!code.includes('isFreeDelivery')) {
    code = code.replace(
      /const \[manageStock, setManageStock\] = useState\(true\);/,
      `const [manageStock, setManageStock] = useState(true);\n  const [isFreeDelivery, setIsFreeDelivery] = useState(false);`
    );

    code = code.replace(
      /setManageStock\(product\.manage_stock === 1\);/,
      `setManageStock(product.manage_stock === 1);\n        setIsFreeDelivery(product.is_free_delivery === 1);`
    );

    code = code.replace(
      /formData\.append\("manage_stock", manageStock \? 1 : 0\);/,
      `formData.append("manage_stock", manageStock ? 1 : 0);\n      formData.append("is_free_delivery", isFreeDelivery ? 1 : 0);`
    );

    const toggleHtml = `

              <Form.Group className="mb-3 d-flex align-items-center gap-3">
                <Form.Label className="mb-0">Free Delivery?</Form.Label>
                <Form.Check
                  type="switch"
                  id="free-delivery-switch"
                  checked={isFreeDelivery}
                  onChange={(e) => setIsFreeDelivery(e.target.checked)}
                />
              </Form.Group>`;

    code = code.replace(
      /(<Form\.Check\s+type="switch"\s+id="manage-stock-switch"[\s\S]*?<\/Form\.Group>)/,
      `$1${toggleHtml}`
    );

    fs.writeFileSync(filePath, code);
    console.log(filePath + ' updated.');
  } else {
    console.log(filePath + ' already updated.');
  }
}

updateFile('c:/laragon/www/kiron-frontend/src/pages/products/AddProduct.jsx');
updateFile('c:/laragon/www/kiron-frontend/src/pages/products/UpdateProduct.jsx');
