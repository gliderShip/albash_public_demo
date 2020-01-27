
# <a href="http://104.248.249.221/"><img align="left" width="50" height="50" src="web/img/car-logo.png" title="1-CAR" alt="1-CAR">1-CAR e-commerce</a>

Albania Software House [Demo Application](http://104.248.249.221/)

### `Software Stack`
---
- ***LAMP, PHP 7.1.\****
- ***Symfony 3.4.\****
- ***Elasticsearch 6.8.\****

### Important general information
    - The current shopping cart will expire after 4 minutes (depending on the `cart_expiration`= 4 parameter) (countdown visible on the checkout page).
    - Upon cart expiration the products will be returned to the inventory.
    - The cart is created upon first product add. Product removal will not delete/recreate the current active cart. (or associated expiration).
    - The client will be able to finalize the purchase by paying the original price if the product price goes UP while the cart is not expired and the product was in cart! The client will be notified.
    - The client will receive an automatic discount if the product price goes DOWN while the cart is not expired! The client will be notified.
    - The client can shop anonymously, but will be required to login/register before checkout.
    - When a registered client logs in, if a cart was left open from a previous client sesssion (and not expired), that cart will subsitute the eventual client cart created from the anonymous shopping before logging in. The client will be notified.
    - A cron job runs every minute to expire orders&items not purchased before the timeout (default cart_expiration is 4 minutes).
    - Instalation instructions can be provided upon request. Test fixtures are generated automaticaly.
    - Please let me konw if you encounter issues or bugs. I will fix & redeploy the demo app.
    
### [Homepage](http://104.248.249.221/)
---

  #### `Search`
  - Home page `search` is implemeted through *Elasticsearch* .
  - A *fuzzy search* is done across all product properties.
  - An ajax request will be fired automatically if query >= 3 characters.
  - If the query return *No Results* `all` the products will be shown (as opposed to `none`).
  - The *Elasticsearch Index* will be updated automatically upon entities *creation*/*deletion*/*update*. 

  #### `Product Listing`
  - For a product to be listed on the homepage it should be:
    1. Listed on the `Inventory`.
    2. `enabled` on the `Inventory`.
  - Products whose inventory `quantity=0` will be listed but `not purchasable`.

  #### [Checkout page/ Shopping cart](http://104.248.249.221/checkout/)
  - Current cart with the products and total can be purchased.
  - It is possible to update/delete current cart products&quantities.
  - Order History for logged-in users is also shown on this page.

    
 ### `Users & Role Hirarchy`
---
- `ROLE_SUPER_ADMIN`
  - `ROLE_ALLOWED_TO_SWITCH`
   - `ROLE_ADMIN`
     - `ROLE_STAFF`
        - `ROLE_CONTRIBUTOR`
          - `ROLE_GUEST`
            - `ROLE_USER`
   - `ROLE_CLIENT`
     - `ROLE_USER`
  
  #### Current Users
  
<table>
    <thead>
      <tr>
          <th>Usernane</th>
          <th>Password</th>
          <th>Role</th>
          <th>Info</th>
      </tr>
    </thead>
      <tbody>
      <tr>
          <td>client</td>
          <td>client</td>
          <td><code>ROLE_CLIENT</code></td>
          <td>
              Can purchase/finalize orders. Default role on registration. <em><b>No Backend access.</b></em>
          </td>
      </tr>
      <tr>
          <td>superadmin</td>
          <td>superadmin</td>
          <td><code>ROLE_SUPER_ADMIN</code></td>
          <td>Create/Impersonate/Edit/Delete Users. +Edit Orders&Items. Can purchase/finalize orders. <em><b>Can NOT delete orders or order-items.</b></em> +<code>ROLE_ADMIN</code> inherited permissions.</td>
      </tr>
      <tr>
          <td>admin</td>
          <td>admin</td>
          <td><code>ROLE_ADMIN</code></td>
          <td><em><b>Can NOT purchase/finalize orders</b></em>. Missing <code>ROLE_CLIENT</code>. +Export Users.  +<code>ROLE_STAFF</code> inherited permissions.</td>
      </tr>
      <tr>
          <td>staff</td>
          <td>staff</td>
          <td><code>ROLE_STAFF</code></td>
          <td><em><b>Can NOT purchase/finalize orders</b></em>. +List/+View Users, +List/+View/+Export Orders&Items. +Export, +Delete the rest. +<code>ROLE_CONTRIBUTOR</code> inherited permissions.</td>
      </tr>
      <tr>
          <td>contributor</td>
          <td>contributor</td>
          <td><code>ROLE_CONTRIBUTOR</code></td>
          <td><em><b>Can NOT purchase/finalize orders</b></em>. +Create/Edit entities, -Export/-Delete entities. -Users, -Orders, -Items. +<code>ROLE_GUEST</code> inherited permissions.</td>
      </tr>
      <tr>
          <td>guest</td>
          <td>guest</td>
          <td><code>ROLE_GUEST</code></td>
          <td>Backend guest. <em><b>Can NOT purchase/finalize orders.</b></em> Can only +List and +View entities. -Orders, -Items, -Users</td>
      </tr>
      </tbody>
</table>

 ### `Links`
---
- ***Home Page*** - http://104.248.249.221/
- ***Registration Page*** - http://104.248.249.221/register/
- ***Login*** - http://104.248.249.221/login
- ***Backend Admin Dashboard*** - http://104.248.249.221/admin/dashboard