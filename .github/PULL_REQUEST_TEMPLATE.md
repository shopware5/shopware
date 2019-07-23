<!--
Thank you for contributing to Shopware! Please fill out this description template to help us to process your pull request.

Please make sure to fulfil our contribution guideline (https://developers.shopware.com/contributing/contribution-guideline/).
-->

### 1. Why is this change necessary?
When adding accessories to the cart, the value of the "sAddAccessoriesQuantity" parameter is not treated as an array, despite the note in Line 511 explicitly saying:
 
"* @param sAddAccessoriesQuantity = List of article quantities separated by ;"

So this is a function that is expected here, but is never executed because it is not implemented.

### 2. What does this change do, exactly?
The value of "sAddAccessoriesQuantity" gets exploded, just like the first parameter "sAddAccessories".

### 3. Describe each step to reproduce the issue or behaviour.


### 4. Please link to the relevant issues (if any).


### 5. Which documentation changes (if any) need to be made because of this PR?
None - in fact, the documentation is incorrect until this change is done.

### 6. Checklist

- [x] I have written tests and verified that they fail without my change
- [x] I have squashed any insignificant commits
- [x] This change has comments for package types, values, functions, and non-obvious lines of code
- [x] I have read the contribution requirements and fulfil them.