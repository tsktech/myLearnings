https://stackoverflow.com/questions/7075923/resync-git-repo-with-new-gitignore-file
### rm all files ###
    git rm -r --cached .
### add all files as per new .gitignore ###
    git add .
### now, commit for new .gitignore to apply ###
    git commit -m ".gitignore is now working"

**(make sure to commit first your changes you want to keep, to avoid any incident as jball037 comments below.**

The --cached option will keep your files untouched on your disk though.)

You also have other more fine-grained solution in the blog post ["Making Git ignore already-tracked files":
](http://aralbalkan.com/2389)

    git rm --cached `git ls-files -i --exclude-standard`

