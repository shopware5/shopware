@listing @seo @basicSettings @configChange
Feature: Listing Seo BasicSettings

    @browsing
    Scenario: I should see canonical, index and follow in emotion world page for category with no pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 0
        And I am on the listing page for category 5

        Then I should see canonical link "genusswelten"
        And I should not see pagination metas
        And I should see robots metas "index" and "follow"

    @browsing
    Scenario: I see no canonical, noindex and follow in listing page 1 for category with no pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 0
        And I am on the listing page for category 5 on page 1

        Then I should not see canonical link
        And I should not see pagination metas
        And I should see robots metas "noindex" and "follow"

    @browsing
    Scenario: I see canonical, index and follow in emotion world page for category with pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 1
        And I am on the listing page for category 5

        Then I should see canonical link "genusswelten"
        And I should see next page meta with link "genusswelten" and page 1
        And I should see robots metas "index" and "follow"

    @browsing
    Scenario: I see no canonical, noindex, follow and no next in listing page 1 for category with pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 1
        And I am on the listing page for category 5 on page 1

        Then I should not see canonical link
        And I should see next page meta with link "genusswelten" and page 2
        And I should not see prev page meta
        And I should see robots metas "noindex" and "follow"

    @browsing
    Scenario: I see no canonical, index, follow, prev and next in listing page 2 for category with pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 1
        And I am on the listing page for category 5 on page 2

        Then I should not see canonical link
        And I should see prev page meta with link "genusswelten" and page 1
        And I should see next page meta with link "genusswelten" and page 3
        And I should see robots metas "noindex" and "follow"

    @browsing
    Scenario: I see a canonical, noindex, follow and prev in last category page with pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 1
        And I am on the listing page for category 5 on page 4

        Then I should not see canonical link
        And I should see prev page meta with link "genusswelten" and page 3
        And I should not see next page meta
        And I should see robots metas "noindex" and "follow"

    @browsing
    Scenario: I see no canonical, noindex, follow and prev in last category page with pagination SEO
        Given the config value of "seoIndexPaginationLinks" is 1
        And I am on the listing page for category 11 on page 2

        Then I should not see canonical link
        And I should see prev page meta with link "genusswelten/tees-und-zubehoer" and page 1
        And I should not see next page meta
        And I should see robots metas "noindex" and "follow"

    @browsing
    Scenario: I see a canonical, index, follow for listing page 1 of category with pagination seo when paging parameters aren't blacklisted
        Given the config value of "seoIndexPaginationLinks" is 1
        And the config value of "seoqueryblacklist" is "sPerPage,sSupplier,sFilterProperties,n,s,f"
        And I am on the listing page for category 5

        Then I should see canonical link "genusswelten"
        And I should see next page meta with link "genusswelten" and page 1
        And I should see robots metas "index" and "follow"

    @browsing
    Scenario: I see a canonical, index, follow for listing page 2 of category with pagination seo when paging parameters aren't blacklisted
        Given the config value of "seoIndexPaginationLinks" is 1
        And the config value of "seoqueryblacklist" is "sPerPage,sSupplier,sFilterProperties,n,s,f"
        And I am on the listing page for category 5 on page 2

        Then I should see canonical link "genusswelten/?p=2"
        And I should see prev page meta with link "genusswelten" and page 1
        And I should see next page meta with link "genusswelten" and page 3
        Then I should see robots metas "index" and "follow"
