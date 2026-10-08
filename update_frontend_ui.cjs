const fs = require('fs');

function updateFile(filePath) {
  let code = fs.readFileSync(filePath, 'utf8');

  // Insert Free Delivery toggle right after the closing div of Manage Stock toggle
  const manageStockToggleHtml = `</button>
                </div>`;
  const freeDeliveryToggleHtml = `</button>
                </div>

                {/* Free Delivery Toggle */}
                <div className="flex justify-between items-center gap-3 mb-3 p-3 bg-gray-50 rounded-lg border">
                  <div className="flex-1 min-w-0">
                    <label className="text-sm font-medium">Free Delivery</label>
                    <p className="text-xs text-gray-500">
                      Enable this to waive delivery charges for this product.
                    </p>
                  </div>
                  <button
                    type="button"
                    onClick={() => setIsFreeDelivery((prev) => !prev)}
                    className={\`relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors \${isFreeDelivery ? "bg-[#13565e]" : "bg-gray-300"
                      }\`}
                  >
                    <span
                      className={\`inline-block h-4 w-4 transform rounded-full bg-white transition-transform \${isFreeDelivery ? "translate-x-6" : "translate-x-1"
                        }\`}
                    />
                  </button>
                </div>`;

  if (!code.includes('Free Delivery Toggle')) {
    code = code.replace(manageStockToggleHtml, freeDeliveryToggleHtml);
    fs.writeFileSync(filePath, code);
    console.log(filePath + ' UI updated.');
  } else {
    console.log(filePath + ' UI already updated.');
  }
}

updateFile('c:/laragon/www/kiron-frontend/src/pages/products/AddProduct.jsx');
updateFile('c:/laragon/www/kiron-frontend/src/pages/products/UpdateProduct.jsx');
