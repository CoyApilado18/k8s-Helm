# Introduction  

Welcome to my documentation of the Kubernetes challenge Extras -> [Package Everything in Helm](https://cloudresumechallenge.dev/docs/extensions/kubernetes-challenge/?utm_source=substack&utm_medium=email#package-everything-in-helm). This demo is for learning purposes only and a great way to gain a hands-on knowledge learning basic fundamentals packaging your Kubernetes cluster of your application into one single deployable unit.  

# Key concepts of Helm
- What is [Helm](https://helm.sh/)?  

In a nutshell, Helm is a package and release manager for Kubernetes. It uses "Charts" to package and version Kubernetes resources.  

Charts - The package (directory) containing templates, default values and metadata (Chart.yaml). This directory containes templates, values.yaml and Chart.yaml. 

Values - Configurable parameters in values.yaml that customizes a chart in different environments.  

Release - A specific installed instance of a chart in a cluster or a "named instance" of a chart running in a cluster. You can have multiple releases for the same chart and for every install/upgrades for the release (increments) it is called a revision number.  

Repository - A published collection of Charts that you can install from an app store for Helm charts. 

# Context
- There's already a containerized Ecommerce app that is part of this Kubernetes Challenge built for this Helm project -see my GitHub repo [learning-app-ecommerce-K8s-Resume-Challenge](https://github.com/CoyApilado18/learning-app-ecommerce-K8s-Resume-Challenge.git) and this is what we will use Helm for to package this Ecommerce webapp. Feel free to clone or fork my GitHub repo. It's a fully functional demo app where all the Kubernetes manifest are tested and ready for deployment to your Kubernetes cluster and you can use it for this Helm demo.

# Goal
- Utilize Helm to package your application, making deployment and management on Kubernetes clusters more efficient and scalable.

# Commands and Notes

