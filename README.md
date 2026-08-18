# Introduction  

Welcome to my documentation of the Kubernetes challenge Extras -> [Package Everything in Helm](https://cloudresumechallenge.dev/docs/extensions/kubernetes-challenge/?utm_source=substack&utm_medium=email#package-everything-in-helm). This demo is for learning purposes only and a great way to gain a hands-on knowledge learning basic fundamentals of Helm, packaging your application powered by Kubernetes into one single deployable unit.  

Side Note: There's a containerized Ecommerce app that is part of this Kubernetes Challenge built for this Helm project -see my GitHub repo [learning-app-ecommerce-K8s-Resume-Challenge](https://github.com/CoyApilado18/learning-app-ecommerce-K8s-Resume-Challenge.git) and this is what we will use Helm for to package the Ecommerce webapp. Feel free to clone or fork my GitHub repo. It's a fully functional demo app where all the Kubernetes manifests are tested and ready for deployment to your Kubernetes cluster and you can use it for this Helm demo.  

What's more, if you want to build your own local kubeadm homelab for your Kubernetes cluster with minimum spec requirements to test this out, feel free to clone or fork my GitHub repo [Build-your-local-Kubernetes-cluster](https://github.com/CoyApilado18/Build-your-local-Kubernetes-cluster.git). It's free and all open source. :)

# Key concepts of Helm
- What is [Helm](https://helm.sh/)?  

In a nutshell, Helm is a package and release manager for Kubernetes. It uses "Charts" to package and version Kubernetes resources.  

Charts - The package (directory) containing templates, default values and metadata (Chart.yaml). This directory containes templates, values.yaml and Chart.yaml. 

Values - Configurable parameters in values.yaml that customizes a chart in different environments.  

Release - A specific installed instance of a chart in a cluster or a "named instance" of a chart running in a cluster. You can have multiple releases for the same chart and for every install/upgrades for the release (increments) it is called a revision number.  

Repository - A published collection of Charts that you can install from an app store for Helm charts. 

# Context
- Package a containerized application that is managed by Kubernetes using Helm.

# Goal
- Utilize Helm to package your application, making deployment and management on Kubernetes clusters more efficient and scalable. A single-stack deployment that includes the web application and database in one release. 

What “one stack” means?  
One Helm release (ecomwebapp) creates:  
- 1 Namespace (ecomwebapp)
- 1 Secret (db-credentials)
- 2 ConfigMaps (db-init-cm, app-config)
- 1 PersistentVolume + 1 PersistentVolumeClaim (for MySQL data)
- 1 MariaDB Deployment + 1 Service
- 1 DB init Job
- 1 Web app Deployment + 1 Service
- 1 HorizontalPodAutoscaler  

All of these resources are managed as one logical application stack: web app + MariaDB + initialization logic.

# Commands and Notes
Understand The Architecture of our Ecommerce webapp  
- The DB and web app share the same namespace: `helm`.
- The web app uses the DB service DNS name: `mariadb-service.helm.svc.cluster.local`.
- The DB init job loads schema and sample products.
  - The DB-init Job depends on normal resources:
    - app-config ConfigMap.
    - db-init-cm ConfigMap.
    - db-credentials Secret.
    - mariadb-service.
    - The MariaDB Pod.
- The web app depends on ConfigMap and Secret values.

### Install Helm in Ubuntu 22.04 as this is the OS I'm using

1. Update package lists 
```bash
sudo apt update
```

2. Install pre-requisites  
```bash
sudo apt install -y curl apt-transport-https
```

3. Download and install Helm  
Use the official Helm install script:
```bash
curl https://raw.githubusercontent.com/helm/helm/main/scripts/get-helm-3 | bash
```

4. Verify installation  
```bash
helm version
```  

NOTE: Before using Helm, make sure 'kubectl' can talk to your cluster.  
```bash
kubectl cluster-info
kubectl get nodes
```
- `kubectl cluster-info` shows cluster control-plane info.
- `kubectl get nodes` confirms your cluster nodes are reachable.  

If `kubectl` does not work yet, Helm will not work either, because Helm deploys into Kubernetes through your kubeconfig.

### Helm

5. Create a Helm "chart". This will create a directory named 'ecomwebapp'-a starter chart structure with sample templates. You would need to delete the sample templates in the templates/ and replace it with the Kuberenets manifests files with the file names we created above for the Helm template target. 
```bash
helm create ecomwebapp
```  

We have 2 manifests files; one for the webapp (ecomweb/) and one for the database (ecomdb/). In my GitHub repo learning-app-ecommerce-K8s-Resume-Challenge, the naming convention of the manifests files is numbered. I just named it this way so that objects will be created respectively. Though there is this concept in Kubernetes called "out-of-order" creation and tt uses declarative reconciliation. Say for example if a deployment for a db pod is created first and is referenced to a Secret object, the pod will be created and will fail to start but, once the Secret object is created, the db pod will start successfully. Where order does matters are; namespace, Helm hooks and CRDs (Custom Resource Definition). Note that those appended by 'db' belongs to the database objects and this is namespaced. We will map these manifests to Helm templates/ and rename these current files to the Helm template target files for more readable context.  

| Current file | Helm template target |
Database files  
| `01-db-namespace.yaml` | `templates/namespace.yaml` |
| `02-db-credentials.yaml` | `templates/db-secret.yaml` |
| `03-db-init-cm.yaml` | `templates/db-init-configmap.yaml` |
| `04-db-app-config.yaml` | `templates/db-app-configmap.yaml` |
| `05-db-pv-pvc.yaml` | `templates/db-pv-pvc.yaml` |
| `06-db-mariadb-deployment.yaml` | `templates/db-mariadb-deployment.yaml` |
| `07-db-mariadb-service.yaml` | `templates/db-mariadb-service.yaml` |
| `08-db-init-job.yaml` | `templates/db-init-job.yaml` |

Webapp files 
| `01-website-deployment.yaml` | `templates/web-deployment.yaml` |
| `02-website-service.yaml` | `templates/web-service.yaml` |
| `03-hpa-ecomwebapp.yaml` | `templates/hpa.yaml` |  

Remove the generated starter files in the templates/ and convert the Kubernetes manifests files from ecomdb/ and ecomweb/ into Helm expressions and save it in the templates/. See templates/ and values.yaml. 
```bash
rm -rf ecomwebapp/templates/deployment.yaml \
      ecomwebapp/templates/service.yaml \
      ecomwebapp/templates/ingress.yaml \
      ecomwebapp/templates/hpa.yaml \
      ecomwebapp/templates/serviceaccount.yaml \
      ecomwebapp/templates/httproute.yaml \
      ecomwebapp/templates/NOTES.txt
```

The Helm chart just:  
- Wraps all of those into one chart.
- Parameterizes them via values.yaml.
- Deploys them together under one release name.

Why this is useful?  
- You can treat the whole ecommerce app (web + DB) as one unit for install, upgrade, and rollback.
- Changing image tags, replicas, feature flags, etc. only requires editing values.yaml and running helm upgrade.
- If something goes wrong, you can roll back the entire stack with helm rollback.  

We can split this later on into separate charts; one chart for the webapp and one chart for the database. 

6. Inspect the chart structure. This will show the chart's files and directories created by Helm.
```bash
ls -R ecomwebapp/
```  

You should expect something like:  
- [Chart.yaml](https://helm.sh/docs/topics/charts/#the-chartyaml-file)  
- [values.yaml](https://helm.sh/docs/chart_best_practices/values/#document-valuesyaml)  
- templates/ - This is where our Kubernetes YAML goes and store the Kubernetes manifest files written with Go template syntax.
- [charts/](https://helm.sh/docs/topics/charts/)  

7. Check YAML and Helm Syntax. This will catch chart and YAML problems.
```bash
helm lint ./ecomwebapp
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/be68aa6ded7509b573b5b96adb8151baeb7df517/images/helm-lint.png)  


8. Render without changing the cluster
```bash
helm template ecomwebapp ./ecomwebapp --namespace ecomwebapp --debug > rendered.yaml
```  

`helm template` will just validate the yaml files in our helm chart -in this case our 'ecomwebapp' helm chart, render them and output into yaml file (rendered.yaml). Note that this does NOT interact with the kubeapi-server and install anything. Again, it just simply validates the yaml files in the chart and render it.  

Inspect the result.
```bash
less rendered.yaml
```

9. Before installing the chart, it's a best practice to '--debug' and '--dry-run' first to check and know the issues associated with your helm chart. We can:
Ask the Kubernetes API server to validate the rendered manifests. NOTE: You will get an error `namespace does not exist` and it's normal. Simply because this is namespaced and the namespace doesn't exist yet. Other than that you will see a preview of the objects that will be created in yaml format in your terminal. 
```bash
kubectl apply --dry-run=server -f rendered.yaml
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/be68aa6ded7509b573b5b96adb8151baeb7df517/images/k-apply-dry-run-server.png)

Or

```bash
helm install <release_name> ./<chart_name> --debug --dry-run
helm install ecomwebapp ./ecomwebapp --debug --dry-run
```


The `kubectl apply --dry-run=server` and `helm install` with `--debug1` & `--dry-run` flags both preview a deployment without creating the resources and validates different layers of the process. 
The `--debug` and `--dry-run` are Helm flags commonly used together to inspect and validate a chart before actually deploying/installing it. The `--dry-run` simulates an operation before without changing the cluster.


10. After both validations succeeded, Install or deploy the Helm Chart. 
```bash
helm install ecomwebapp ./ecomwebapp \
  --namespace helm \
  --create-namespace \
  --wait \
  --timeout 10m
```

Here:  
First `ecomwebapp`: the Helm release name.
`./ecomwebapp`: the chart directory.  
`--namespace helm`: the target namespace.  
`--create-namespace`: creates it if necessary.  
`--wait`: waits for resources to become ready.  
`--timeout 10m`: gives the deployment up to ten minutes.  

11. Check all Kubernetes and resources are created successfully. 
```bash
k -n helm get all
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/be68aa6ded7509b573b5b96adb8151baeb7df517/images/k-get-all.png)  

The Kubernetes objects look healthy as you can see above, but the Helm release status records the result of the previous Helm operation. In my case, revision 2 and 3 was marked failed because the Helm command timed out or a hook failed at that time -I did some troubleshooting earlier as I used `pre-install` and `pre-upgrade` for the db hooks in my db-init-job. The db-init-job is dependent on "normal chart resources" such as: app-config and db-init-cm configmaps, db-credentials secret, mariadb service and mariadb pod. In a nut shell. a `pre-install` hooks run after Helm renders the chart but BEFORE the normal chart resources are created. So it errors out as the `pre-install` hook runs before the normal chart resources are created. I changed this to `post-install` and `post-upgrade` so that the normal chart resources are created first then Helm runs the db-init-job. With this, Helm does not automatically change a failed revision to deployed merely because the Pods later recover.
```bash
helm status ecomwebapp -n helm
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/be68aa6ded7509b573b5b96adb8151baeb7df517/images/helm-status.png)  

11. Run this command in a separate terminal
```bash
k -n helm port-forward svc/ecom-web-svc 8080:80
```

12. Open a browser then checkout our Ecommerce webapp via localhost and you should see our webapp.
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/be68aa6ded7509b573b5b96adb8151baeb7df517/images/ecommerce-website.png)


### Publish as an OCI artifact and store to GHCR

13. Before publishing, check chart contents. 
```bash
tree ecomwebapp
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/7a9c14986d61a6127ca8602233890fdc3b60cfa9/images/tree-ecomwebapp.png)

Your Chart.yaml currently identifies it as an application chart named ecomwebapp with chart version 0.1.0 and application version v2.

14. Update Chart.yaml before publishing. Since you have now built the fixed web image testyoc/ecom-web-helm:v1, this is a good first chart release:
```bash
apiVersion: v2  
name: ecomwebapp  
description: Helm chart for the ecommerce web application and MariaDB  
type: application  
version: 0.1.0  
appVersion: "v1"  
```

Important distinction:  
`version: 0.1.0` = chart version; update it whenever templates or defaults change.  
`appVersion: "v1"` = informational version of your application image.  
Helm uses `version` in the packaged chart filename:  
`ecomwebapp-0.1.0.tgz`

14. Make values.yaml match the deployed web image. For a reproducible chart release, store your actual image defaults:
```bash
web:
  image:
    repository: testyoc/ecom-web-helm
    tag: v1
    pullPolicy: IfNotPresent
```
Our template renders the image from `web.image.repository` and `web.image.tag`.

15. Do not publish real secrets
Our current chart includes demo DB credentials in values.yaml and renders them into a Kubernetes Secret using stringData.

For this learning project, you can publish placeholders, but do not publish reusable real credentials. Use clearly non-production defaults:
values.yaml
```bash
credentials:
  secretName: db-credentials
  username: CHANGE_ME
  password: CHANGE_ME
  rootPassword: CHANGE_ME
```

A more production-ready next step would support an existing Secret or External Secrets. We can improve that after publishing the first chart.

### Validate the Chart

16. Perform step 7 (helm lint) & 8 (helm template) again to validate then confirm the application image by running the command:
```bash
grep -n 'image: "testyoc/ecom-web-helm:v1"' rendered.yaml
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/7a9c14986d61a6127ca8602233890fdc3b60cfa9/images/grep-rendered.png)

### Package the Chart
17. Run:
```bash
helm package ./ecomwebapp
```
Helm reads Chart.yaml, validates the chart, and creates:
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/7a9c14986d61a6127ca8602233890fdc3b60cfa9/images/helm-packaged.png)

Inspect the package contents:
```bash
tar -tzf ecomwebapp-0.1.0.tgz
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/7a9c14986d61a6127ca8602233890fdc3b60cfa9/images/tar-tzf.png)

### Authenticate Helm with GHCR
18. To push from your laptop, create a GitHub Personal Access Token with package-write permission.
For a classic PAT, give it:
`write:packages`
`read:packages`
`repo` only if the GitHub repository is private and GitHub requests it for package association
Keep the token private; do not commit it or paste it into a shell history.

Export it only for your terminal session:
```bash
export CR_PAT='PASTE_YOUR_GITHUB_TOKEN_HERE'
```
Log in:  
```bash
echo "$CR_PAT" | helm registry login ghcr.io \
  --username <your-github-username> \
  --password-stdin
```

19. Push the packaged chart to GHCR
```bash
helm push ecomwebapp-0.1.0.tgz \
  oci://ghcr.io/<github-username>/helm-charts
```
![image alt](https://github.com/CoyApilado18/k8s-Helm/blob/7a9c14986d61a6127ca8602233890fdc3b60cfa9/images/pushed-packaged-ghcrio.png)

You can also see the package in your GitHub ghcr.io in "Packages". :)  

And If you want to pull/download the chart locally run:
```bash
helm pull \
  oci://ghcr.io/coyapilado18/helm-charts/ecomwebapp \
  --version 0.1.1
  ```
This only downloads the package:
```bash
ecomwebapp-0.1.1.tgz
``` 

Extract it:
```bash
tar -xzf ecomwebapp-0.1.1.tgz
```

### Then refer to steps 7,8,9 and 10 (helm install) to install the Chart.
 
Or the easiest, just use the `helm` pull with `--untar` flag if you are pulling the chart from my GHCR. The command below tells Helm to download and extract it in one command the install the chart.
```bash
helm pull \
  oci://ghcr.io/coyapilado18/helm-charts/ecomwebapp \
  --version 0.1.1 \
  --untar
```

# Thank you and happy Helming! :)